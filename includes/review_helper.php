<?php

/**
 * Lấy danh sách từ ngữ bị cấm
 * 
 * @return array
 */
function getBannedWords() {
    static $banned_words = null;
    
    if ($banned_words === null) {
        $banned_words = require __DIR__ . '/../config/banned_words.php';
    }
    
    return $banned_words;
}

/**
 * Kiểm tra nội dung đánh giá có chứa từ ngữ vi phạm hay không
 * 
 * @param string $content Nội dung cần kiểm tra
 * @return array ['is_valid' => bool, 'banned_words' => array]
 */
function checkReviewContent($content) {
    // Nếu không có nội dung thì coi như hợp lệ
    if (empty(trim($content))) {
        return [
            'is_valid' => true,
            'banned_words' => []
        ];
    }

    $banned_words = getBannedWords();
    $found_words = [];
    $content = mb_strtolower($content, 'UTF-8'); // Chuyển về chữ thường
    
    foreach ($banned_words as $word) {
        if (mb_strpos($content, mb_strtolower($word, 'UTF-8')) !== false) {
            $found_words[] = $word;
        }
    }
    
    return [
        'is_valid' => empty($found_words),
        'banned_words' => $found_words
    ];
}

/**
 * Kiểm tra và tự động duyệt đánh giá
 * 
 * @param array $review Thông tin đánh giá
 * @return array ['status' => string, 'message' => string]
 */
function autoApproveReview($review) {
    // Kiểm tra rating có hợp lệ không (từ 1-5 sao)
    if (!isset($review['rating']) || $review['rating'] < 1 || $review['rating'] > 5) {
        return [
            'status' => 'rejected',
            'message' => 'Đánh giá phải có từ 1-5 sao'
        ];
    }

    // Kiểm tra nội dung comment nếu có
    if (!empty(trim($review['comment']))) {
        $content_check = checkReviewContent($review['comment']);
        
        if (!$content_check['is_valid']) {
            return [
                'status' => 'rejected',
                'message' => 'Đánh giá chứa từ ngữ không phù hợp: ' . implode(', ', $content_check['banned_words'])
            ];
        }
    }
    
    // Mọi điều kiện đều hợp lệ
    return [
        'status' => 'approved',
        'message' => 'Đánh giá hợp lệ và đã được duyệt tự động'
    ];
} 