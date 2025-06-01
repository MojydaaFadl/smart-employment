# Advanced ATS Resume Analysis System
import re
import json
import os
import logging
from datetime import datetime
from typing import List, Optional, Dict, Any
from pathlib import Path
from pydantic import BaseModel, Field, validator
from pdfplumber import open as pdf_open
from langchain_groq import ChatGroq
from langchain_core.prompts import ChatPromptTemplate
from langchain_core.output_parsers import JsonOutputParser
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import tempfile
import shutil
import requests
import uvicorn
import importlib.util
import sys

# git API Key from config
path = "C:/Users/AMIZING/Desktop/config.py"
module_name = "config"

spec = importlib.util.spec_from_file_location(module_name, path)
config = importlib.util.module_from_spec(spec)
sys.modules[module_name] = config
spec.loader.exec_module(config)

# Initialize logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize FastAPI app
app = FastAPI()

# Configure CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# 1. Enhanced PDF text extraction with error handling
def extract_text_from_pdf(file_path: str) -> List[str]:
    """استخراج النص من ملف PDF عبر URL"""
    try:
        # تنزيل الملف مؤقتًا إذا كان URL
        if file_path.startswith('http'):
            response = requests.get(file_path)
            response.raise_for_status()
            
            with tempfile.NamedTemporaryFile(delete=False) as tmp_file:
                tmp_file.write(response.content)
                tmp_path = tmp_file.name
            
            text = extract_text_from_local_pdf(tmp_path)  # دالة مساعدة
            os.unlink(tmp_path)  # حذف الملف المؤقت
            return text
            
        # إذا كان مسارًا محليًا
        elif os.path.exists(file_path):
            return extract_text_from_local_pdf(file_path)
            
        else:
            raise FileNotFoundError(f"File not found: {file_path}")
            
    except Exception as e:
        logger.error(f"PDF extraction error: {str(e)}")
        raise

def extract_text_from_local_pdf(path: str) -> List[str]:
    """معالجة الملفات المحلية"""
    with pdf_open(path) as pdf:
        return [page.extract_text() or "" for page in pdf.pages]

def clean_text(text: str) -> str:
    """Clean text from extra spaces and special characters"""
    text = re.sub(r'\s+', ' ', text)
    text = text.strip()
    return text

# 2. Resume analysis model with validation
class ResumeAnalysis(BaseModel):
    candidate_info: Dict[str, Any] = Field(..., description="Candidate basic information")
    work_experience: List[Dict[str, Any]] = Field(..., description="Work experience details")
    skills_analysis: Dict[str, Any] = Field(..., description="Skills evaluation")
    education: Dict[str, str] = Field(..., description="Education background")
    summary: Dict[str, Any] = Field(..., description="Analysis summary")
    
    @validator('candidate_info')
    def validate_candidate_info(cls, v):
        if 'full_name' not in v:
            raise ValueError("Candidate full name is required")
        if 'email' not in v or '@' not in v['email']:
            raise ValueError("Valid email address is required")
        return v

# API Request Model
class AnalysisRequest(BaseModel):
    job_description: str
    cv_paths: List[str]
    job_id: str
    employer_id: str

# 3. LLM initialization with error handling
def initialize_llm():
    """Initialize language model with error handling"""
    try:
        return ChatGroq(
            model_name="llama3-70b-8192",
            api_key=config.my_api_key,
            temperature=0
        )
    except Exception as e:
        logger.error(f"LLM initialization error: {str(e)}")
        raise RuntimeError(f"Failed to initialize language model: {str(e)}")

# 4. Enhanced analysis prompt
def create_analysis_prompt():
    """Create analysis prompt with clear instructions"""
    return ChatPromptTemplate.from_messages([
        ("system", """
You are an expert ATS resume evaluator. Analyze the resume carefully according to these instructions:

1. Extract basic information:
   - Full name (must be on a clear line)
   - Email (must contain @)
   - Phone number (if available)
   - Location (if available)

2. Work experience (required for each position):
   - Job title (e.g., "Web Developer")
   - Company name
   - Time period (MM/YYYY - MM/YYYY)
   - Duration in months/years (auto-calculated)
   - 3 key achievements (bullet points)
   - Technologies used

3. Skills:
   - Extract all technical skills
   - Determine proficiency level (Beginner, Intermediate, Advanced)
   - Match against job requirements

4. Education:
   - Degree (e.g., "Bachelor's in Information Technology")
   - Institution
   - Graduation year

📌 Important notes:
- If information is missing, write "Not available"
- Ensure accurate experience duration calculation
- Do not invent missing information

{format_instructions}
"""),
        ("user", """
Job Description:
{job_description}

Resume Content:
{resume}
""")
    ])

# 5. Resume analysis with validation
def analyze_resume(resume_text: str, job_description: str, chain) -> Dict[str, Any]:
    """Analyze resume with comprehensive validation"""
    try:
        logger.info("Starting resume analysis...")
        parser = JsonOutputParser(pydantic_object=ResumeAnalysis)
        result = chain.invoke({
            "resume": resume_text,
            "job_description": job_description,
            "format_instructions": parser.get_format_instructions()
        })
        
        if not result.get('candidate_info', {}).get('full_name'):
            raise ValueError("Could not extract candidate name from resume")
        
        logger.info(f"Successfully analyzed: {result['candidate_info']['full_name']}")
        return result
    except Exception as e:
        logger.error(f"Analysis error: {str(e)}")
        return {
            "error": str(e),
            "timestamp": datetime.now().isoformat(),
            "raw_output": result if 'result' in locals() else None
        }

def calculate_matching_score(analysis: Dict[str, Any], job_description: str) -> float:
    """Calculate matching score between resume and job description"""
    logger.info("Calculating matching score...")
    score = 0
    
    # Normalize job description
    job_desc_lower = job_description.lower()
    
    # Check for required skills
    required_skills = set()
    skill_patterns = {
        'Python': r'python',
        'Flutter': r'flutter',
        'CSS': r'css',
        'JavaScript': r'javascript|js'
    }
    
    for skill, pattern in skill_patterns.items():
        if re.search(pattern, job_desc_lower, re.IGNORECASE):
            required_skills.add(skill)
    
    resume_skills = set(analysis.get('skills_analysis', {}).get('technical_skills', []))
    matching_skills = required_skills & resume_skills
    score += len(matching_skills) * 10
    logger.info(f"Skills match: {matching_skills} (+{len(matching_skills)*10} points)")
    
    # Education requirement
    if re.search(r'bachelor', job_desc_lower, re.IGNORECASE):
        if 'bachelor' in analysis.get('education', {}).get('degree', '').lower():
            score += 20
            logger.info("Bachelor's degree match (+20 points)")
    
    # Experience calculation
    exp_match = re.search(r'(\d+)\+?\s*(?:years|سنوات)', job_description, re.IGNORECASE)
    if exp_match:
        required_exp = int(exp_match.group(1))
        total_exp = sum([exp.get('duration_months', 0) for exp in analysis.get('work_experience', [])]) / 12
        logger.info(f"Experience: Required {required_exp} years, Found {total_exp:.1f} years")
        
        if total_exp >= required_exp:
            score += 30
            logger.info(f"Experience requirement met (+30 points)")
        else:
            score += (total_exp / required_exp) * 30
            logger.info(f"Partial experience ({total_exp/required_exp:.0%} +{(total_exp/required_exp)*30:.1f} points)")
    
    logger.info(f"Total score: {score:.1f}")
    return round(score, 1)

# 6. Save results to file
def save_results(analysis: Dict[str, Any]) -> str:
    """Save analysis results to JSON file"""
    try:
        if "error" in analysis:
            raise ValueError(analysis["error"])
        
        today = datetime.now().strftime("%Y-%m-%d")
        save_dir = Path("analysis_results") / today
        save_dir.mkdir(parents=True, exist_ok=True)
        
        candidate_name = analysis['candidate_info']['full_name']
        safe_name = re.sub(r'[\\/*?:"<>|]', "_", candidate_name)
        timestamp = datetime.now().strftime("%H%M%S")
        filename = f"analysis_{safe_name}_{timestamp}.json"
        filepath = save_dir / filename
        
        with open(filepath, "w", encoding="utf-8") as f:
            json.dump(analysis, f, ensure_ascii=False, indent=2)
        
        logger.info(f"Saved results to: {filepath}")
        return str(filepath)
    except Exception as e:
        logger.error(f"Failed to save results: {str(e)}")
        return ""

# API Endpoint for resume analysis
@app.post("/analyze-resumes/")
async def analyze_resumes_endpoint(request: AnalysisRequest):
    logger.info(f"Received analysis request for job {request.job_id}")
    try:
        llm = initialize_llm()
        parser = JsonOutputParser(pydantic_object=ResumeAnalysis)
        prompt = create_analysis_prompt()
        chain = prompt | llm | parser
        
        results = []
        for cv_path in request.cv_paths:
            try:
                logger.info(f"Processing CV: {cv_path}")
                resume_text = "\n".join(extract_text_from_pdf(cv_path))
                analysis = analyze_resume(resume_text, request.job_description, chain)
                analysis['cv_path'] = cv_path
                
                if "error" not in analysis:
                    analysis['matching_score'] = calculate_matching_score(analysis, request.job_description)
                    save_results(analysis)
                else:  # إضافة حقل النسبة في حالة الخطأ
                    analysis['matching_score'] = 0.0
                
                results.append(analysis)
            except Exception as e:
                logger.error(f"Error processing {cv_path}: {str(e)}")
                results.append({
                    "error": str(e),
                    "cv_path": cv_path,
                    "matching_score": 0.0,
                    "timestamp": datetime.now().isoformat()
                })
        
        # ترتيب النتائج الناجحة فقط
        successful_results = [r for r in results if "error" not in r]
        if successful_results:
            successful_results.sort(key=lambda x: x['matching_score'], reverse=True)
        
        return {
            "job_id": request.job_id,
            "employer_id": request.employer_id,
            "analysis_date": datetime.now().isoformat(),
            "results": results,
            "top_candidates": successful_results[:3] if successful_results else []
        }
        
    except Exception as e:
        logger.error(f"API error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# Main execution
if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8000)