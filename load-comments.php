<?php
session_start();
require_once 'comment-functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'يجب تسجيل الدخول']);
    exit();
}

if (!isset($_GET['job_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'معرف الوظيفة مطلوب']);
    exit();
}

$jobId = (int)$_GET['job_id'];
$comments = getCommentsForJob($jobId);

// تحويل التاريخ إلى صيغة "منذ x وقت"
foreach ($comments as &$comment) {
    $comment['time_ago'] = humanTiming(strtotime($comment['created_at']));
    $comment['full_name'] = $comment['first_name'] . ' ' . $comment['last_name'];
}

echo json_encode(['status' => 'success', 'comments' => $comments]);

function humanTiming($time) {
    $time = time() - $time;
    $time = ($time < 1) ? 1 : $time;
    $tokens = array(
        31536000 => 'سنة',
        2592000 => 'شهر',
        604800 => 'أسبوع',
        86400 => 'يوم',
        3600 => 'ساعة',
        60 => 'دقيقة',
        1 => 'ثانية'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits > 1)?'':'');
    }
}
?>