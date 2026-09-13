<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
define('DATA_DIR', __DIR__ . '/data');
define('AUCTION_FILE', DATA_DIR . '/auction.json');
define('BIDS_FILE', DATA_DIR . '/bids.json');
define('BALANCE_FILE', DATA_DIR . '/balance.txt');
define('QUESTIONS_FILE', DATA_DIR . '/submitted_questions.json');

// Ensure data directory exists
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

$action = $_GET['action'] ?? ($_POST['action'] ?? null);

switch ($action) {
    case 'getCurrentAuction':
        getCurrentAuction();
        break;
    case 'placeBid':
        placeBid();
        break;
    case 'submitQuestion':
        submitQuestion();
        break;
    case 'getBalance':
        getBalance($_GET['username'] ?? 'guest');
        break;
    case 'initAuction':
        initAuction();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function getCurrentAuction() {
    if (!file_exists(AUCTION_FILE)) {
        // Initialize with default
        $auction = [
            'id' => 1,
            'question' => 'What is the best approach to optimize LLM inference on consumer hardware?',
            'description' => 'Current active auction question - Place your bid to win processing time',
            'current_bid' => 10,
            'current_bidder' => 'system',
            'bids' => [],
            'created_at' => date('Y-m-d H:i:s'),
            'processing_time' => '12:00'
        ];
        file_put_contents(AUCTION_FILE, json_encode($auction, JSON_PRETTY_PRINT));
    }
    
    $auction = json_decode(file_get_contents(AUCTION_FILE), true);
    
    // Load recent bids
    if (file_exists(BIDS_FILE)) {
        $allBids = json_decode(file_get_contents(BIDS_FILE), true) ?? [];
        $auction['bids'] = array_slice($allBids, -10);
    } else {
        $auction['bids'] = [];
    }
    
    echo json_encode(['success' => true, 'auction' => $auction]);
}

function placeBid() {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? null;
    $amount = $input['amount'] ?? null;
    $question = $input['question'] ?? '';
    
    if (!$username || !$amount || $amount < 10) {
        echo json_encode(['success' => false, 'error' => 'Invalid bid data']);
        return;
    }
    
    // Check balance
    $balance = getUserBalance($username);
    if ($balance < $amount) {
        echo json_encode(['success' => false, 'error' => 'Insufficient balance']);
        return;
    }
    
    // Load auction
    $auction = json_decode(file_get_contents(AUCTION_FILE), true);
    
    if ($amount <= $auction['current_bid']) {
        echo json_encode(['success' => false, 'error' => 'Bid must be higher than current bid']);
        return;
    }
    
    // Update auction
    $auction['current_bid'] = $amount;
    $auction['current_bidder'] = $username;
    file_put_contents(AUCTION_FILE, json_encode($auction, JSON_PRETTY_PRINT));
    
    // Record bid
    $bids = [];
    if (file_exists(BIDS_FILE)) {
        $bids = json_decode(file_get_contents(BIDS_FILE), true) ?? [];
    }
    
    $bids[] = [
        'username' => $username,
        'amount' => $amount,
        'question' => $question,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents(BIDS_FILE, json_encode($bids, JSON_PRETTY_PRINT));
    
    echo json_encode(['success' => true, 'message' => 'Bid placed successfully']);
}

function submitQuestion() {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? null;
    $question = $input['question'] ?? null;
    
    if (!$username || !$question) {
        echo json_encode(['success' => false, 'error' => 'Invalid submission']);
        return;
    }
    
    $questions = [];
    if (file_exists(QUESTIONS_FILE)) {
        $questions = json_decode(file_get_contents(QUESTIONS_FILE), true) ?? [];
    }
    
    $questions[] = [
        'username' => $username,
        'question' => $question,
        'status' => 'pending_review',
        'submitted_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents(QUESTIONS_FILE, json_encode($questions, JSON_PRETTY_PRINT));
    
    echo json_encode(['success' => true, 'message' => 'Question submitted for review']);
}

function getBalance($username) {
    $balances = [];
    if (file_exists(BALANCE_FILE)) {
        $lines = file(BALANCE_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode(':', $line);
            if (count($parts) >= 2) {
                $balances[trim($parts[0])] = floatval($parts[1]);
            }
        }
    }
    
    $balance = $balances[$username] ?? 0;
    
    // Count bids
    $bidsCount = 0;
    if (file_exists(BIDS_FILE)) {
        $bids = json_decode(file_get_contents(BIDS_FILE), true) ?? [];
        $bidsCount = count(array_filter($bids, function($b) use ($username) {
            return $b['username'] === $username;
        }));
    }
    
    echo json_encode([
        'success' => true,
        'username' => $username,
        'balance' => $balance,
        'bids_count' => $bidsCount
    ]);
}

function initAuction() {
    // Initialize default auction if none exists
    if (!file_exists(AUCTION_FILE)) {
        $auction = [
            'id' => 1,
            'question' => 'Sample Question',
            'description' => 'Sample Description',
            'current_bid' => 10,
            'current_bidder' => 'system',
            'bids' => [],
            'created_at' => date('Y-m-d H:i:s'),
            'processing_time' => '12:00'
        ];
        file_put_contents(AUCTION_FILE, json_encode($auction, JSON_PRETTY_PRINT));
    }
    
    echo json_encode(['success' => true, 'message' => 'Auction initialized']);
}
?>