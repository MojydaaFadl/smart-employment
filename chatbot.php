<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>مساعد التوظيف الذكي</title>
  <link rel="stylesheet" href="css/fontawesome/css/all.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    @font-face {
      font-family: "Cairo";
      src: url("fonts/Cairo\ Regular\ 400.ttf");
    }
    /* إعادة ضبط عام */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Cairo';
    }

    html, body {
      height: 100%;
      width: 100%;
      overflow: hidden;
      background-color: #f8f9fa;
    }

    /* هيدر الصفحة */
    .page-header {
      background: linear-gradient(135deg, #007bff, #0062cc);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: relative;
      z-index: 10;
      padding: 10px;
    }

    .page-header h1 {
      font-size: 1.3rem;
    }

    .back-btn {
      background: none;
      border: none;
      color: white;
      font-size: 1.2rem;
      cursor: pointer;
      position: absolute;
      left: 20px;
    }

    /* منطقة المحادثة الرئيسية */
    .chat-container {
      height: calc(100% - 70px);
      display: flex;
      flex-direction: column;
      position: relative;
    }

    .chat-area {
      flex: 1;
      padding: 15px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }

    /* رسالة الترحيب المركزية */
    .welcome-container {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      width: 90%;
      max-width: 500px;
    }

    .welcome-message {
      font-size: 1.2rem;
      color: #333;
      margin-bottom: 30px;
      font-weight: 500;
    }

    /* بطاقات الأسئلة السريعة المركزية */
    .quick-actions-container {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
      width: 100%;
      max-width: 400px;
      margin: 0 auto;
    }

    .quick-action-card {
      background-color: white;
      border-radius: 12px;
      padding: 12px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      border: 1px solid #e9ecef;
    }

    .quick-action-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
      background-color: #f8f9fa;
    }

    .quick-action-card i {
      font-size: 1.2rem;
      color: #007bff;
      margin-bottom: 8px;
      display: block;
    }

    .quick-action-card span {
      font-size: 0.8rem;
      color: #495057;
      font-weight: 500;
    }

    /* تنسيقات الرسائل */
    .message {
      max-width: 80%;
      padding: 12px 16px;
      border-radius: 18px;
      line-height: 1.5;
      position: relative;
      animation: fadeIn 0.3s ease;
      word-break: break-word;
      margin-bottom: 12px;
      font-size: 0.95rem;
    }

    .bot-message {
      background-color: white;
      border-top-right-radius: 5px;
      align-self: flex-start;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
      border: 1px solid #e9ecef;
    }

    .user-message {
      background: linear-gradient(135deg, #007bff, #0062cc);
      color: white;
      border-top-left-radius: 5px;
      align-self: flex-end;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .message-time {
      font-size: 0.65rem;
      opacity: 0.7;
      margin-top: 5px;
      display: block;
      text-align: left;
    }

    /* منطقة الإدخال */
    .input-area {
      padding: 12px 15px;
      background-color: white;
      border-top: 1px solid #e9ecef;
      display: flex;
      gap: 10px;
      align-items: center;
      box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.03);
      position: relative;
      z-index: 5;
    }

    .input-area input {
      flex: 1;
      padding: 10px 15px;
      border: 1px solid #e9ecef;
      border-radius: 25px;
      outline: none;
      font-size: 0.95rem;
      transition: all 0.3s;
      background-color: #f8f9fa;
    }

    .input-area input:focus {
      border-color: #007bff;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .action-btn {
      background: linear-gradient(135deg, #007bff, #0062cc);
      color: white;
      border: none;
      border-radius: 50%;
      width: 42px;
      height: 42px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .action-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    }

    .action-btn i {
      font-size: 1rem;
    }

    /* مؤشر الكتابة */
    .typing-indicator {
      display: flex;
      gap: 5px;
      padding: 12px 16px;
      background-color: white;
      border-radius: 18px;
      align-self: flex-start;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
      border: 1px solid #e9ecef;
      margin-bottom: 12px;
    }

    .typing-indicator span {
      width: 7px;
      height: 7px;
      background-color: #adb5bd;
      border-radius: 50%;
      display: inline-block;
      animation: bounce 1.5s infinite ease-in-out;
    }

    .typing-indicator span:nth-child(2) {
      animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
      animation-delay: 0.4s;
    }

    /* تأثيرات الحركة */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes bounce {
      0%, 100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-4px);
      }
    }

    /* شريط التمرير */
    .chat-area::-webkit-scrollbar {
      width: 6px;
    }

    .chat-area::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    .chat-area::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 3px;
    }

    .chat-area::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }

    /* تصميم متجاوب */
    @media (max-width: 576px) {
      .quick-actions-container {
        grid-template-columns: 1fr;
        max-width: 250px;
      }

      .welcome-message {
        font-size: 1.1rem;
        margin-bottom: 20px;
      }

      .quick-action-card {
        padding: 10px;
      }

      .quick-action-card i {
        font-size: 1.1rem;
      }

      .quick-action-card span {
        font-size: 0.75rem;
      }
    }
  </style>
</head>
<body>
  <!-- هيدر الصفحة -->
  <header class="page-header">
    <button class="back-btn" onclick="navigateTo()">
      <i class="fas fa-arrow-right"></i>
    </button>
    <h1>مساعد التوظيف الذكي</h1>
  </header>

  <!-- منطقة المحادثة الرئيسية -->
  <main class="chat-container">
    <!-- رسالة الترحيب المركزية -->
    <div class="welcome-container" id="welcomeContainer">
      <div class="welcome-message" id="welcomeMessage">
        كيف يمكنني مساعدتك اليوم؟
      </div>

      <!-- بطاقات الأسئلة السريعة المركزية -->
      <div class="quick-actions-container" id="quickActionsContainer">
        <!-- سيتم ملؤها بواسطة الجافاسكريبت بناءً على نوع المستخدم -->
      </div>
    </div>

    <!-- منطقة المحادثة الفعلية -->
    <div class="chat-area" id="chatArea">
      <!-- سيتم إضافة الرسائل هنا ديناميكيًا -->
    </div>

    <!-- منطقة الإدخال -->
    <div class="input-area">
      <input type="text" id="userInput" placeholder="اكتب سؤالك هنا..." autofocus>
      <button class="action-btn" id="sendBtn" title="إرسال">
        <i class="fas fa-paper-plane"></i>
      </button>
    </div>
  </main>

  <script>
    // العناصر الرئيسية
    const chatArea = document.getElementById('chatArea');
    const userInput = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    const welcomeContainer = document.getElementById('welcomeContainer');
    const welcomeMessage = document.getElementById('welcomeMessage');
    const quickActionsContainer = document.getElementById('quickActionsContainer');
    
    // تحديد نوع المستخدم (صاحب عمل أو باحث عن عمل)
    let userType = 'employee'; // افتراضي باحث عن عمل
    const urlParams = new URLSearchParams(window.location.search);
    
    // التحقق من نوع المستخدم من خلال URL أو الجلسة
    if (urlParams.has('employer')) {
      userType = 'employer';
    } else if (urlParams.has('employee')) {
      userType = 'employee';
    }
    
    // تهيئة واجهة المستخدم بناءً على نوعه
    document.addEventListener('DOMContentLoaded', function() {
      setupUserInterface();
    });

    // إعداد واجهة المستخدم بناءً على نوعه
    function setupUserInterface() {
      if (userType === 'employer') {
        welcomeMessage.textContent = 'مرحباً بك صاحب العمل! كيف يمكنني مساعدتك اليوم؟';
        setupEmployerQuickActions();
      } else {
        welcomeMessage.textContent = 'مرحباً بك باحث عن العمل! كيف يمكنني مساعدتك اليوم؟';
        setupEmployeeQuickActions();
      }
    }

    // إعداد الأسئلة السريعة لأصحاب العمل
    function setupEmployerQuickActions() {
      const quickActions = [
        {
          icon: 'fas fa-bullhorn',
          text: 'كيف أنشر وظيفة جديدة؟',
          question: 'كيف يمكنني نشر وظيفة جديدة؟'
        },
        {
          icon: 'fas fa-users',
          text: 'كيف أتصفح السير الذاتية؟',
          question: 'كيف يمكنني تصفح السير الذاتية للمتقدمين؟'
        },
        {
          icon: 'fas fa-file-alt',
          text: 'كيف أتحكم في إعلاناتي؟',
          question: 'كيف يمكنني إدارة الوظائف المنشورة؟'
        },
        {
          icon: 'fas fa-search',
          text: 'كيف أبحث عن مرشحين؟',
          question: 'كيف يمكنني البحث عن مرشحين مناسبين؟'
        }
      ];
      
      renderQuickActions(quickActions);
    }

    // إعداد الأسئلة السريعة للباحثين عن عمل
    function setupEmployeeQuickActions() {
      const quickActions = [
        {
          icon: 'fas fa-search',
          text: 'كيف أبحث عن وظيفة؟',
          question: 'كيف أبحث عن وظيفة؟'
        },
        {
          icon: 'fas fa-file-export',
          text: 'كيف أتقدم لوظيفة؟',
          question: 'كيف أتقدم لوظيفة؟'
        },
        {
          icon: 'fas fa-heart',
          text: 'كيف أضيف وظيفة للمفضلة؟',
          question: 'كيف أضيف وظيفة للمفضلة؟'
        },
        {
          icon: 'fas fa-tasks',
          text: 'كيف أتتبع طلبات التقديم؟',
          question: 'كيف أتتبع طلبات التقديم؟'
        }
      ];
      
      renderQuickActions(quickActions);
    }

    // عرض الأسئلة السريعة
    function renderQuickActions(actions) {
      quickActionsContainer.innerHTML = '';
      
      actions.forEach(action => {
        const card = document.createElement('div');
        card.className = 'quick-action-card';
        card.innerHTML = `
          <i class="${action.icon}"></i>
          <span>${action.text}</span>
        `;
        card.onclick = () => sendQuickQuestion(action.question);
        quickActionsContainer.appendChild(card);
      });
    }

    // دالة العودة الى الصفحة الرئيسية
    function navigateTo() {
      if (userType === 'employer') {
        window.location.href = 'for-employers/employer-dashboard.php';
      } else {
        window.location.href = 'employee-dashboard.php';
      }
    }

    // إرسال رسالة عند الضغط على Enter
    userInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        sendMessage();
      }
    });

    // إرسال رسالة عند النقر على زر الإرسال
    sendBtn.addEventListener('click', sendMessage);

    // إرسال سؤال سريع عند النقر على بطاقة المساعدة
    function sendQuickQuestion(question) {
      // إخفاء رسالة الترحيب والبطاقات
      welcomeContainer.style.display = 'none';

      // إرسال السؤال
      userInput.value = question;
      sendMessage();
    }

    // إرسال رسالة
    function sendMessage() {
      const message = userInput.value.trim();
      if (message !== '') {
        // إخفاء رسالة الترحيب والبطاقات عند أول رسالة
        if (welcomeContainer.style.display !== 'none') {
          welcomeContainer.style.display = 'none';
        }

        // إضافة رسالة المستخدم
        addMessage(message, 'user');

        // مسح حقل الإدخال
        userInput.value = '';

        // تركيز على حقل الإدخال
        userInput.focus();

        // عرض مؤشر الكتابة
        const typingIndicator = showTypingIndicator();

        // إضافة رد الروبوت بعد تأخير
        setTimeout(() => {
          // إخفاء مؤشر الكتابة
          chatArea.removeChild(typingIndicator);

          // الحصول على رد الروبوت
          const botResponse = getBotResponse(message);
          addMessage(botResponse, 'bot');

          // التمرير إلى الأسفل
          scrollToBottom();
        }, 1000);
      }
    }

    // إضافة رسالة جديدة
    function addMessage(text, sender) {
      const messageElement = document.createElement('div');
      messageElement.classList.add('message', `${sender}-message`);

      const time = new Date().toLocaleTimeString('ar-EG', {
        hour: '2-digit',
        minute: '2-digit'
      });

      messageElement.innerHTML = `
      ${text}
      <span class="message-time">${time}</span>
      `;

      chatArea.appendChild(messageElement);
      scrollToBottom();
    }

    // عرض مؤشر الكتابة
    function showTypingIndicator() {
      const typingElement = document.createElement('div');
      typingElement.classList.add('typing-indicator');
      typingElement.innerHTML = `
      <span></span>
      <span></span>
      <span></span>
      `;
      chatArea.appendChild(typingElement);
      scrollToBottom();
      return typingElement;
    }

    // التمرير إلى آخر رسالة
    function scrollToBottom() {
      chatArea.scrollTop = chatArea.scrollHeight;
    }

    // ردود الروبوت المخصصة بناءً على نوع المستخدم
    function getBotResponse(message) {
      const lowerMessage = message.toLowerCase();

      // ردود عامة لكلا النوعين
      if (lowerMessage.includes('مرحبا') || lowerMessage.includes('اهلا') || lowerMessage.includes('السلام')) {
        return "مرحباً بك في مساعد التوظيف الذكي! أنا هنا لمساعدتك في استخدام المنصة.";
      } else if (lowerMessage.includes('شكرا') || lowerMessage.includes('متشكر')) {
        return "على الرحب والسعة! هل هناك أي شيء آخر تحتاج إليه؟";
      } else if (lowerMessage.includes('اسمك') || lowerMessage.includes('ما اسمك')) {
        return "أنا مساعد التوظيف الذكي، يمكنك مناداتي كما تريد!";
      }

      // ردود خاصة بأصحاب العمل
      if (userType === 'employer') {
        if (lowerMessage.includes('نشر') || lowerMessage.includes('وظيفة') || lowerMessage.includes('إعلان')) {
          return `لنشر وظيفة جديدة:<br>
          1. انتقل إلى قسم "إعلانات الوظائف"<br>
          2. اضغط على "أعلن عن وظيفتك"<br>
          3. املأ نموذج إعلان الوظيفة<br>
          4. اضغط على زر "نشر الوظيفة"<br><br>
          يمكنك تعديل أو حذف الوظائف من قسم "الوظائف الخاصة بي"`;
          
        } else if (lowerMessage.includes('سيرة') || lowerMessage.includes('مرشح') || lowerMessage.includes('متقدم')) {
          return `لتصفح السير الذاتية:<br>
          1. انتقل إلى قسم "البحث عن السيرة الذاتية"<br>
          2. استخدم عوامل التصفية للبحث عن المرشحين<br>
          3. اضغط على أي سيرة ذاتية لعرض التفاصيل<br>
          4. يمكنك حفظ السير الذاتية للرجوع إليها لاحقاً`;
          
        } else if (lowerMessage.includes('إدارة') || lowerMessage.includes('تحكم') || lowerMessage.includes('وظائف')) {
          return `لإدارة الوظائف المنشورة:<br>
          1. انتقل إلى قسم "الوظائف الخاصة بي"<br>
          2. اضغط على زر التعديل لتغيير تفاصيل الوظيفة<br>
          3. اضغط على زر الحذف لإزالة الوظيفة<br>
          4. يمكنك تغيير حالة الوظيفة (نشطة/غير نشطة)`;
          
        } else if (lowerMessage.includes('بحث') || lowerMessage.includes('مرشحين') || lowerMessage.includes('مطابق')) {
          return `للبحث عن مرشحين:<br>
          1. انتقل إلى قسم "البحث عن السيرة الذاتية"<br>
          2. ابحث بالكلمات المفتاحية (مهارات، مؤهل، خبرة)<br>
          3. استخدم عوامل التصفية لتحسين النتائج<br>
          4. احفظ نتائج البحث للرجوع إليها لاحقاً`;
        }
      }
      // ردود خاصة بالباحثين عن عمل
      else {
        if (lowerMessage.includes('بحث') || lowerMessage.includes('وظيفة') || lowerMessage.includes('وظائف')) {
          return `للبحث عن الوظائف:<br>
          1. اكتب كلمات البحث في المربع العلوي<br>
          2. اختر الدولة إذا أردت<br>
          3. اضغط على زر البحث<br><br>
          ستظهر لك النتائج التي يمكنك تصفيتها حسب نوع الوظيفة أو الراتب.`;
          
        } else if (lowerMessage.includes('تقدم') || lowerMessage.includes('تقديم') || lowerMessage.includes('قدم')) {
          return `للتقديم على الوظائف:<br>
          1. ابحث عن الوظيفة المناسبة<br>
          2. اضغط على زر "تقديم الآن"<br>
          3. سيتم إرسال سيرتك الذاتية تلقائياً<br>
          4. يمكنك تتبع حالة طلبك في قسم "الوظائف المتقدم لها"`;
          
        } else if (lowerMessage.includes('مفضلة') || lowerMessage.includes('حفظ') || lowerMessage.includes('لاحقا')) {
          return `لإدارة الوظائف المفضلة:<br>
          1. اضغط على أيقونة القلب في بطاقة الوظيفة<br>
          2. لعرضها، انتقل إلى قسم "الوظائف المفضلة"<br>
          3. يمكنك التقديم مباشرة من هناك`;
          
        } else if (lowerMessage.includes('تتبع') || lowerMessage.includes('حالة') || lowerMessage.includes('طلب')) {
          return `لمتابعة طلبات التقديم:<br>
          1. انتقل إلى قسم "الوظائف المتقدم لها"<br>
          2. ستظهر جميع طلباتك مع حالتها<br>
          3. اضغط على أي طلب لمزيد من التفاصيل`;
        }
      }

      // إذا لم يتم التعرف على السؤال
      if (userType === 'employer') {
        return "لم أفهم سؤالك تماماً. هل تقصد أحد هذه المواضيع؟<br>- نشر وظيفة جديدة<br>- تصفح السير الذاتية<br>- إدارة الوظائف المنشورة<br>- البحث عن مرشحين";
      } else {
        return "لم أفهم سؤالك تماماً. هل تقصد أحد هذه المواضيع؟<br>- البحث عن وظائف<br>- التقديم للوظائف<br>- إدارة المفضلة<br>- تتبع الطلبات";
      }
    }
  </script>
</body>
</html>