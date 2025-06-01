<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  die("You must log in first");
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "smart_employment";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create CV</title>
  <script src="js/jspdf.umd.min.js"></script>
  <style>
    body {
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    #loading-container {
      text-align: center;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      max-width: 500px;
      width: 90%;
    }
    .spinner {
      border: 5px solid #f3f3f3;
      border-top: 5px solid #3498db;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      animation: spin 1s linear infinite;
      margin: 0 auto 20px;
    }
    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }
    #status {
      margin-top: 20px;
      font-size: 18px;
      color: #333;
    }
    #error-message {
      color: #e74c3c;
      margin-top: 20px;
      padding: 15px;
      border: 1px solid #e74c3c;
      border-radius: 5px;
      background-color: #fdecea;
      display: none;
    }
    .success-message {
      color: #27ae60;
      margin-top: 20px;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div id="loading-container">
    <div class="spinner"></div>
    <div id="status">
      Creating CV...
    </div>
    <div id="error-message"></div>
    <div id="success-message" class="success-message" style="display:none;"></div>
  </div>

  <img id="preview-image" style="display: none;" alt="Profile Picture">

  <script src="js/storage.js"></script>
  <script>
    const {
      jsPDF
    } = window.jspdf;

    // Update loading status
    function updateStatus(message) {
      document.getElementById('status').textContent = message;
      console.log(message);
    }

    // Show error
    function showError(message) {
      const errorEl = document.getElementById('error-message');
      errorEl.textContent = message;
      errorEl.style.display = 'block';
      document.querySelector('.spinner').style.display = 'none';
      updateStatus("Error occurred while creating CV");
    }

    // Show success
    function showSuccess(message) {
      const successEl = document.getElementById('success-message');
      successEl.textContent = message;
      successEl.style.display = 'block';
      document.querySelector('.spinner').style.display = 'none';
      updateStatus("");
    }

    // Load CV data
    function loadCVData() {
      try {
        const cvDataString = localStorage.getItem('cvDataForPDF');

        if (!cvDataString) {
          throw new Error("No saved CV data found");
        }

        const cvData = JSON.parse(cvDataString);

        // Validate basic data
        if (!cvData.personalInfo || !cvData.personalInfo.firstName || !cvData.personalInfo.lastName) {
          throw new Error("CV data is incomplete");
        }

        return cvData;
      } catch (error) {
        showError("Error loading data: " + error.message);
        return null;
      }
    }

    // Load image
    function loadImage(img) {
      return new Promise((resolve, reject) => {
        if (!img.src) {
          resolve(null);
          return;
        }

        if (img.complete && img.naturalWidth !== 0) {
          resolve();
        } else {
          img.onload = () => resolve();
          img.onerror = () => reject(new Error("Failed to load profile picture"));
        }
      });
    }

    // Generate CV PDF
    async function generateCV() {
      try {
        updateStatus("Loading data...");
        const cvData = loadCVData();
        if (!cvData) return;

        updateStatus("Creating PDF file...");
        const doc = new jsPDF( {
          orientation: 'portrait',
          unit: 'mm',
          format: 'a4'
        });

        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();
        const leftColWidth = pageWidth * 0.37;
        const rightColWidth = pageWidth * 0.63;
        const margin = 5;
        let yPos = 15; // Start position without profile picture

        // 1. Left column (blue) - Basic info
        doc.setFillColor(44, 62, 80);
        doc.rect(0, 0, leftColWidth, pageHeight, 'F');

        // 2. Add profile picture only if available
        if (cvData.personalInfo?.profilePicture) {
          updateStatus("Loading profile picture...");
          const img = document.getElementById('preview-image');
          let imageUrl = cvData.personalInfo.profilePicture;

          // Clean URL
          if (imageUrl.startsWith('url("') && imageUrl.endsWith('")')) {
            imageUrl = imageUrl.substring(5, imageUrl.length - 2);
          } else if (imageUrl.startsWith('url(') && imageUrl.endsWith(')')) {
            imageUrl = imageUrl.substring(4, imageUrl.length - 1);
          }
          imageUrl = imageUrl.replace(/^["']|["']$/g, '');

          img.src = imageUrl;

          try {
            await loadImage(img);
            if (img.complete && img.naturalWidth !== 0) {
              const imgSize = 30;
              const x = leftColWidth / 2 - imgSize / 2;
              yPos = 15;

              const canvas = document.createElement('canvas');
              const size = 200;
              canvas.width = size;
              canvas.height = size;
              const ctx = canvas.getContext('2d');

              ctx.fillStyle = '#2c3e50';
              ctx.fillRect(0, 0, size, size);

              ctx.beginPath();
              ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
              ctx.closePath();
              ctx.clip();

              ctx.drawImage(img, 0, 0, size, size);

              const imageData = canvas.toDataURL('image/jpeg');
              doc.addImage(imageData, 'JPEG', x, yPos, imgSize, imgSize);
              yPos += imgSize + 10;
            }
          } catch (e) {
            console.log("Failed to load profile image, continuing without it");
          }
        }

        // 3. Add skills
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(12);
        doc.text("Skills", margin, yPos);
        yPos += 7;

        doc.setFontSize(10);
        if (cvData.skills && cvData.skills.length > 0) {
          cvData.skills.forEach(skill => {
            doc.text(`• ${skill.name} (${skill.level})`, margin, yPos);
            yPos += 5;
          });
        } else {
          doc.text("No skills added", margin, yPos);
          yPos += 5;
        }
        yPos += 10;

        // 4. Add achievements
        if (cvData.achievements && cvData.achievements.length > 0) {
          doc.setFontSize(12);
          doc.text("Achievements", margin, yPos);
          yPos += 5;

          doc.setFontSize(10);
          cvData.achievements.forEach(achievement => {
            const text = `• ${achievement.title}: ${achievement.description}`;
            const lines = doc.splitTextToSize(text, leftColWidth - 1 * margin);
            doc.text(lines, margin, yPos);
            yPos += lines.length * 5;
            if (lines.length = 1) {
              yPos += 5;
            } else {
              yPos = yPos - 15;
            }
          });
        }

        // 5. Add interests if available
        if (cvData.interests && cvData.interests.length > 0) {
          doc.setFontSize(12);
          doc.text("Interests", margin, yPos);
          yPos += 7;

          doc.setFontSize(10);
          let interestsText = cvData.interests.join(', ');
          const interestsLines = doc.splitTextToSize(interestsText, leftColWidth - 2 * margin);
          doc.text(interestsLines, margin, yPos);
          yPos += interestsLines.length * 5 + 10;
        }

        // 6. Add languages if available
        if (cvData.languages && cvData.languages.length > 0) {
          doc.setFontSize(12);
          doc.text("Languages", margin, yPos);
          yPos += 7;

          doc.setFontSize(10);
          cvData.languages.forEach(lang => {
            doc.text(`• ${lang.language} (${lang.level})`, margin, yPos);
            yPos += 5;
          });
          yPos += 10;
        }

        // 6. Right column (white) - Detailed info
        doc.setTextColor(0, 0, 0);
        let rightYPos = 15;

        // 7. Name and personal info
        doc.setFontSize(18);
        doc.text(`${cvData.personalInfo.firstName} ${cvData.personalInfo.lastName}`, leftColWidth + margin, rightYPos);
        rightYPos += 7;

        doc.setTextColor(0, 123, 255);
        doc.setFontSize(16);
        doc.text(`${cvData.personalInfo.jobTitle}`, leftColWidth + margin, rightYPos);
        rightYPos += 7;

        // 8. Contact info
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        if (cvData.personalInfo.phone) {
          doc.text(`Phone: ${cvData.personalInfo.countryCode} ${cvData.personalInfo.phone}`, leftColWidth + margin, rightYPos);
          rightYPos += 5;
        }
        if (cvData.personalInfo.email) {
          doc.text(`Email: ${cvData.personalInfo.email}`, leftColWidth + margin, rightYPos);
          rightYPos += 5;
        }
        if (cvData.personalInfo.cityLocation || cvData.personalInfo.countryLocation) {
          doc.text(`Address: ${cvData.personalInfo.cityLocation}, ${cvData.personalInfo.countryLocation}`, leftColWidth + margin, rightYPos);
          rightYPos += 5;
        }

        // 9. Separator line
        doc.setDrawColor(44, 62, 80);
        doc.setLineWidth(0.25);
        doc.line(leftColWidth + margin, rightYPos, pageWidth - margin, rightYPos);
        rightYPos += 10;

        // 10. Personal summary
        if (cvData.personalInfo.summary) {
          doc.setFontSize(14);
          doc.text("Summary", leftColWidth + margin, rightYPos);
          rightYPos += 7;

          doc.setFontSize(10);
          const summaryLines = doc.splitTextToSize(cvData.personalInfo.summary, rightColWidth - 2 * margin);
          doc.text(summaryLines, leftColWidth + margin, rightYPos);
          rightYPos += (summaryLines.length * 5);
        }

        // 11. Separator line
        doc.setDrawColor(44, 62, 80);
        doc.setLineWidth(0.25);
        doc.line(leftColWidth + margin, rightYPos, pageWidth - margin, rightYPos);
        rightYPos += 10;

        // 12. Work experience
        if (cvData.experiences && cvData.experiences.length > 0) {
          updateStatus("Adding work experiences...");

          doc.setFontSize(14);
          doc.text("Work Experience", leftColWidth + margin, rightYPos);
          rightYPos += 7;

          doc.setFontSize(10);

          cvData.experiences.forEach(exp => {
            const endDateText = exp.stillWorking ? 'Present': `${exp.endMonthDisplay} ${exp.endYearDisplay}`;

            doc.setFont(undefined, 'bold');
            doc.text(exp.jobTitle, leftColWidth + margin, rightYPos);
            rightYPos += 5;

            doc.setFont(undefined, 'normal');
            const companyInfo = `${exp.companyName} | ${exp.cityDisplay || exp.city}, ${exp.countryDisplay || exp.country} | ${exp.startMonthDisplay} ${exp.startYearDisplay} - ${endDateText}`;
            doc.text(companyInfo, leftColWidth + margin, rightYPos);
            rightYPos += 5;

            if (exp.description) {
              const lines = doc.splitTextToSize(`• ${exp.description}`, rightColWidth - 2 * margin);
              doc.text(lines, leftColWidth + margin, rightYPos);
              rightYPos += (lines.length * 5) + 5;
            }
          });
          doc.setDrawColor(44,
            62,
            80);
          doc.setLineWidth(0.5);
          doc.line(leftColWidth + margin,
            rightYPos,
            pageWidth - margin,
            rightYPos);
          rightYPos += 10;
        }

        // 13. Education
        if (cvData.educations && cvData.educations.length > 0) {
          updateStatus("Adding educational qualifications...");



          doc.setFontSize(14);
          doc.text("Education", leftColWidth + margin, rightYPos);
          rightYPos += 7;

          doc.setFontSize(10);

          cvData.educations.forEach(edu => {
            doc.setFont(undefined, 'bold');
            doc.text(edu.degree, leftColWidth + margin, rightYPos);
            rightYPos += 5;

            doc.setFont(undefined, 'normal');
            const eduInfo = `${edu.institution} | ${edu.city}, ${edu.country} | Graduation Year: ${edu.graduationYear}`;
            doc.text(eduInfo, leftColWidth + margin, rightYPos);
            rightYPos += 5;

            if (edu.fieldOfStudy) {
              doc.text(`Field of Study: ${edu.fieldOfStudy}`, leftColWidth + margin, rightYPos);
              rightYPos += 5;
            }
            rightYPos += 5;
          });
        }

        // 14. Add "By Smart Employment" at the bottom
        doc.setFontSize(8);
        doc.setTextColor(150,
          150,
          150);
        doc.text(
          "By Smart Employment System",
          leftColWidth + margin,
          pageHeight - 3
        );

        // Save file locally first
        const fileName = `${cvData.personalInfo.firstName}_${cvData.personalInfo.lastName}_CV.pdf`;

        updateStatus("Preparing file for upload...");
        const pdfBlob = doc.output('blob');
        const pdfBase64 = await new Promise((resolve) => {
          const reader = new FileReader();
          reader.onloadend = () => resolve(reader.result);
          reader.readAsDataURL(pdfBlob);
        });

        updateStatus("Uploading file to server...");
        const response = await fetch('upload_created_cv.php',
          {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              user_id: '<?php echo $_SESSION["user_id"]; ?>',
              file_name: fileName,
              file_data: pdfBase64.split(',')[1]
            })
          });

        if (!response.ok) {
          throw new Error("Server response error");
        }

        const result = await response.json();
        if (!result.success) {
          throw new Error(result.message || "File upload failed");
        }

        showSuccess("تم إنشاء السيرة الذاتية بنجاح!");
        updateStatus("إعادة التوجيه إلى لوحة المعلومات...");
        clearFormData();
        setTimeout(() => {
          window.location.href = '../employee-dashboard.php?cv_upload=success';
        }, 3000);

      } catch (error) {
        showError(error.message);
        console.error("Error:", error);
      }
    }

    // Start process automatically when page loads
    window.addEventListener('DOMContentLoaded', generateCV);
  </script>
</body>
</html>