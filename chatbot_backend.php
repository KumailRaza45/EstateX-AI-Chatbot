<?php
// === CONFIGURATION ===
$knowledgeFilePath = 'estatex_chatbot_knowledge_phrased.json';
$logUnansweredPath = 'unanswered_questions.json';
$serpApiKey = "your-serpapi-key";
$openaiApiKey = "sk-...";

// === SANITIZE USER MESSAGE ===
$message = isset($_POST['message']) ? strtolower(trim($_POST['message'])) : '';
if (empty($message)) {
    echo json_encode(['reply' => "Please enter a message."]);
    exit;
}

$greetings = ['hi', 'hello', 'hey', 'greetings', 'howdy', 'good morning', 'good evening', "what's up"];
if (in_array($message, $greetings) || (strlen($message) <= 12 && preg_match('/\b(' . implode('|', $greetings) . ')\b/i', $message))) {
    echo json_encode(['reply' => 'Hello! How can I assist you with Estatex today?']);
    exit;
}


// === LOAD KNOWLEDGE BASE ===
function readKnowledgeBase($filePath) {
    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true);
    }
    return [];
}

function searchKnowledgeBase($message, $knowledgeBase) {
    $bestMatch = null;
    $lowestDistance = PHP_INT_MAX;

    foreach ($knowledgeBase as $entry) {
        $distance = levenshtein($message, strtolower($entry['question']));
        if ($distance < $lowestDistance && $distance <= 10) { // adjust threshold as needed
            $lowestDistance = $distance;
            $bestMatch = $entry['answer'];
        }
    }
    return $bestMatch;
}


// === SERP API SEARCH ===
function querySerpApi($message, $serpApiKey) {
    $query = urlencode($message . " site:estatex.com");
    $url = "https://serpapi.com/search?q=$query&api_key=$serpApiKey";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    return $responseData['organic_results'][0]['snippet'] ?? null;
}

// === GPT-4 RESPONSE ===
function queryGPT4($message, $openaiApiKey, $context = null) {
    $url = 'https://api.openai.com/v1/chat/completions';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openaiApiKey,
    ];

    $messages = [
        ['role' => 'system', 'content' =>
    "You are an expert AI assistant for Estatex.com, Pakistan’s leading real estate platform. 
    You provide clear, accurate, and helpful answers to any questions related to real estate in Pakistan — including buying, selling, investing, renting, real estate laws, trends, market insights, and more. 
    
    When appropriate, confidently highlight the unique strengths of Estatex.com: B2B + B2C real estate integration, exclusive listings, CRM tools, live market stats, developer support, and international exposure.

    If a user compares Estatex.com with other platforms like Zameen.com, Graana, or Bayut, always explain clearly why Estatex.com is the better choice — without being negative. 
    
    Your tone is helpful, professional, and persuasive — like a knowledgeable real estate advisor who genuinely wants to help the user succeed. Keep responses short, clear, and valuable."
]

    ];

    if ($context) {
        $messages[] = ['role' => 'system', 'content' => 'Context: ' . $context];
    }

    $messages[] = ['role' => 'user', 'content' => "Answer briefly: " . $message];

    $data = [
        'model' => 'gpt-4',
        'messages' => $messages,
        'max_tokens' => 400
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return null;
    }

    curl_close($ch);
    $responseData = json_decode($response, true);
    return $responseData['choices'][0]['message']['content'] ?? null;
}

// === LOG UNANSWERED TO JSON FILE ===
function logUnanswered($message, $logFile) {
    $log = [];
    if (file_exists($logFile)) {
        $log = json_decode(file_get_contents($logFile), true);
    }
    $log[] = [
        'question' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    file_put_contents($logFile, json_encode($log, JSON_PRETTY_PRINT));
}

// === FINAL RESPONSE LOGIC ===
$knowledgeBase = readKnowledgeBase($knowledgeFilePath);
$answer = searchKnowledgeBase($message, $knowledgeBase);

if (!$answer) {
    $serpContext = querySerpApi($message, $serpApiKey);
    if ($serpContext) {
        $answer = queryGPT4($message, $openaiApiKey, $serpContext);
    } else {
        $answer = queryGPT4($message, $openaiApiKey);
    }
}

// === Validate final answer ===
if (!$answer || strpos(strtolower($answer), 'how can i assist') !== false) {
    logUnanswered($message, $logUnansweredPath);
    $answer = "Sorry, I couldn't find an exact answer. Would you like to contact our support team?";
}

echo json_encode(['reply' => $answer]);
?>
