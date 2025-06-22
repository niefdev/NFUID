<?php

class NFUID {
    const BASE64_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
    const TIMESTAMP_BITS = 42;
    const FLAG_BITS = 1;
    const RANDOM_BITS = 23;
    
    private static $BASE64_MAP = null;
    private static $MAX_TIMESTAMP = null;
    private static $MAX_RANDOM = null;
    
    private static function initConstants() {
        if (self::$BASE64_MAP === null) {
            self::$BASE64_MAP = [];
            for ($i = 0; $i < 64; $i++) {
                self::$BASE64_MAP[self::BASE64_CHARS[$i]] = $i;
            }
            
            self::$MAX_TIMESTAMP = self::bigIntSubtract(self::bigIntLeftShift('1', self::TIMESTAMP_BITS), '1');
            self::$MAX_RANDOM = self::bigIntSubtract(self::bigIntLeftShift('1', self::RANDOM_BITS), '1');
        }
    }
    
    // Manual BigInt arithmetic functions
    private static function bigIntAdd($a, $b) {
        $a = (string)$a;
        $b = (string)$b;
        
        $result = '';
        $carry = 0;
        $i = strlen($a) - 1;
        $j = strlen($b) - 1;
        
        while ($i >= 0 || $j >= 0 || $carry > 0) {
            $digitA = $i >= 0 ? (int)$a[$i] : 0;
            $digitB = $j >= 0 ? (int)$b[$j] : 0;
            
            $sum = $digitA + $digitB + $carry;
            $result = ($sum % 10) . $result;
            $carry = intval($sum / 10);
            
            $i--;
            $j--;
        }
        
        return ltrim($result, '0') ?: '0';
    }
    
    private static function bigIntSubtract($a, $b) {
        $a = (string)$a;
        $b = (string)$b;
        
        if (self::bigIntCompare($a, $b) < 0) {
            return '0';
        }
        
        $result = '';
        $borrow = 0;
        $i = strlen($a) - 1;
        $j = strlen($b) - 1;
        
        while ($i >= 0) {
            $digitA = (int)$a[$i] - $borrow;
            $digitB = $j >= 0 ? (int)$b[$j] : 0;
            
            if ($digitA < $digitB) {
                $digitA += 10;
                $borrow = 1;
            } else {
                $borrow = 0;
            }
            
            $result = ($digitA - $digitB) . $result;
            $i--;
            $j--;
        }
        
        return ltrim($result, '0') ?: '0';
    }
    
    private static function bigIntMultiply($a, $b) {
        $a = (string)$a;
        $b = (string)$b;
        
        if ($a === '0' || $b === '0') return '0';
        
        $result = '0';
        
        for ($i = strlen($b) - 1; $i >= 0; $i--) {
            $digit = (int)$b[$i];
            $temp = '0';
            
            for ($j = strlen($a) - 1; $j >= 0; $j--) {
                $product = $digit * (int)$a[$j];
                $temp = self::bigIntAdd($temp, (string)$product . str_repeat('0', strlen($a) - 1 - $j));
            }
            
            $temp .= str_repeat('0', strlen($b) - 1 - $i);
            $result = self::bigIntAdd($result, $temp);
        }
        
        return $result;
    }
    
    private static function bigIntDivide($a, $b) {
        $a = (string)$a;
        $b = (string)$b;
        
        if ($b === '0') throw new Exception('Division by zero');
        if (self::bigIntCompare($a, $b) < 0) return '0';
        
        $result = '';
        $remainder = '0';
        
        for ($i = 0; $i < strlen($a); $i++) {
            $remainder = self::bigIntAdd(self::bigIntMultiply($remainder, '10'), $a[$i]);
            
            $count = 0;
            while (self::bigIntCompare($remainder, $b) >= 0) {
                $remainder = self::bigIntSubtract($remainder, $b);
                $count++;
            }
            
            $result .= $count;
        }
        
        return ltrim($result, '0') ?: '0';
    }
    
    private static function bigIntModulo($a, $b) {
        $quotient = self::bigIntDivide($a, $b);
        $product = self::bigIntMultiply($quotient, $b);
        return self::bigIntSubtract($a, $product);
    }
    
    private static function bigIntCompare($a, $b) {
        $a = ltrim((string)$a, '0') ?: '0';
        $b = ltrim((string)$b, '0') ?: '0';
        
        if (strlen($a) > strlen($b)) return 1;
        if (strlen($a) < strlen($b)) return -1;
        
        return strcmp($a, $b);
    }
    
    private static function bigIntLeftShift($a, $bits) {
        $a = (string)$a;
        
        for ($i = 0; $i < $bits; $i++) {
            $a = self::bigIntMultiply($a, '2');
        }
        
        return $a;
    }
    
    private static function bigIntRightShift($a, $bits) {
        $a = (string)$a;
        
        for ($i = 0; $i < $bits; $i++) {
            $a = self::bigIntDivide($a, '2');
        }
        
        return $a;
    }
    
    private static function bigIntAnd($a, $b) {
        $a = (string)$a;
        $b = (string)$b;
        
        $binA = self::decimalToBinary($a);
        $binB = self::decimalToBinary($b);
        
        $maxLen = max(strlen($binA), strlen($binB));
        $binA = str_pad($binA, $maxLen, '0', STR_PAD_LEFT);
        $binB = str_pad($binB, $maxLen, '0', STR_PAD_LEFT);
        
        $result = '';
        for ($i = 0; $i < $maxLen; $i++) {
            $result .= ($binA[$i] === '1' && $binB[$i] === '1') ? '1' : '0';
        }
        
        return self::binaryToDecimal($result);
    }
    
    private static function bigIntOr($a, $b) {
        $a = (string)$a;
        $b = (string)$b;
        
        $binA = self::decimalToBinary($a);
        $binB = self::decimalToBinary($b);
        
        $maxLen = max(strlen($binA), strlen($binB));
        $binA = str_pad($binA, $maxLen, '0', STR_PAD_LEFT);
        $binB = str_pad($binB, $maxLen, '0', STR_PAD_LEFT);
        
        $result = '';
        for ($i = 0; $i < $maxLen; $i++) {
            $result .= ($binA[$i] === '1' || $binB[$i] === '1') ? '1' : '0';
        }
        
        return self::binaryToDecimal($result);
    }
    
    private static function bigIntXor($a, $b) {
        $a = (string)$a;
        $b = (string)$b;
        
        $binA = self::decimalToBinary($a);
        $binB = self::decimalToBinary($b);
        
        $maxLen = max(strlen($binA), strlen($binB));
        $binA = str_pad($binA, $maxLen, '0', STR_PAD_LEFT);
        $binB = str_pad($binB, $maxLen, '0', STR_PAD_LEFT);
        
        $result = '';
        for ($i = 0; $i < $maxLen; $i++) {
            $result .= ($binA[$i] !== $binB[$i]) ? '1' : '0';
        }
        
        return self::binaryToDecimal($result);
    }
    
    private static function decimalToBinary($decimal) {
        if ($decimal === '0') return '0';
        
        $binary = '';
        while (self::bigIntCompare($decimal, '0') > 0) {
            $remainder = self::bigIntModulo($decimal, '2');
            $binary = $remainder . $binary;
            $decimal = self::bigIntDivide($decimal, '2');
        }
        
        return $binary;
    }
    
    private static function binaryToDecimal($binary) {
        if ($binary === '0' || $binary === '') return '0';
        
        $decimal = '0';
        $power = '1';
        
        for ($i = strlen($binary) - 1; $i >= 0; $i--) {
            if ($binary[$i] === '1') {
                $decimal = self::bigIntAdd($decimal, $power);
            }
            $power = self::bigIntMultiply($power, '2');
        }
        
        return $decimal;
    }
    
    private static function getSecureRandom() {
        self::initConstants();
        
        $bytes = random_bytes(4);
        $value = unpack('N', $bytes)[1];
        $bigValue = (string)$value;
        
        return self::bigIntAnd($bigValue, self::$MAX_RANDOM);
    }
    
    private static function stretchRandom($random) {
        self::initConstants();
        
        $v = self::bigIntAnd($random, self::$MAX_RANDOM);
        
        $v = self::bigIntXor($v, self::bigIntRightShift($v, 13));
        $v = self::bigIntMultiply($v, '11400714819323198485');
        $v = self::bigIntXor($v, self::bigIntRightShift($v, 12));
        $v = self::bigIntMultiply($v, '13787848793156543417');
        $v = self::bigIntXor($v, self::bigIntRightShift($v, 15));
        
        return self::bigIntAnd($v, self::$MAX_TIMESTAMP);
    }
    
    private static function encodeBitsToBase64($value, $bitCount) {
        $chars = [];
        $remainingBits = $bitCount;
        
        while ($remainingBits > 0) {
            $bitsToTake = min(6, $remainingBits);
            $shift = $remainingBits - $bitsToTake;
            
            $shiftedValue = self::bigIntRightShift($value, $shift);
            $mask = self::bigIntSubtract(self::bigIntLeftShift('1', $bitsToTake), '1');
            $index = (int)self::bigIntAnd($shiftedValue, $mask);
            
            if ($bitsToTake < 6) {
                $index = $index << (6 - $bitsToTake);
            }
            
            $chars[] = self::BASE64_CHARS[$index];
            $remainingBits -= $bitsToTake;
        }
        
        return implode('', $chars);
    }
    
    private static function decodeBase64ToBits($str, $expectedBitCount) {
        self::initConstants();
        
        $value = '0';
        $totalBits = 0;
        
        for ($i = 0; $i < strlen($str); $i++) {
            $char = $str[$i];
            if (!isset(self::$BASE64_MAP[$char])) {
                throw new Exception('Invalid character in string');
            }
            
            $charBits = min(6, $expectedBitCount - $totalBits);
            $charValue = self::$BASE64_MAP[$char];
            
            if ($charBits < 6) {
                $charValue = $charValue >> (6 - $charBits);
            }
            
            $value = self::bigIntOr(self::bigIntLeftShift($value, $charBits), (string)$charValue);
            $totalBits += $charBits;
        }
        
        return $value;
    }
    
    private static function encodeRandomToBase64($random23) {
        return self::encodeBitsToBase64($random23, 23);
    }
    
    public static function generate($hidden = false) {
        self::initConstants();
        
        $now = (string)(int)(microtime(true) * 1000);
        $timestamp = self::bigIntAnd($now, self::$MAX_TIMESTAMP);
        
        $random = self::getSecureRandom();
        
        $flagBit = $hidden ? '1' : '0';
        
        $finalTimestamp = $timestamp;
        
        if ($hidden) {
            $stretched = self::stretchRandom($random);
            $finalTimestamp = self::bigIntXor($timestamp, $stretched);
            $finalTimestamp = self::bigIntAnd($finalTimestamp, self::$MAX_TIMESTAMP);
        }
        
        $combined = self::bigIntOr(
            self::bigIntOr(
                self::bigIntLeftShift($finalTimestamp, 24),
                self::bigIntLeftShift($flagBit, 23)
            ),
            $random
        );
        
        return self::encodeBitsToBase64($combined, 66);
    }
    
    public static function parse($id) {
        self::initConstants();
        
        $value = self::decodeBase64ToBits($id, 66);
        
        $random = self::bigIntAnd($value, self::$MAX_RANDOM);
        $flagBit = self::bigIntAnd(self::bigIntRightShift($value, 23), '1');
        $timestamp = self::bigIntAnd(self::bigIntRightShift($value, 24), self::$MAX_TIMESTAMP);
        
        $isHidden = $flagBit === '1';
        
        if ($isHidden) {
            $stretched = self::stretchRandom($random);
            $timestamp = self::bigIntXor($timestamp, $stretched);
            $timestamp = self::bigIntAnd($timestamp, self::$MAX_TIMESTAMP);
        }
        
        $timestampMs = (int)$timestamp;
        $timestampSec = $timestampMs / 1000;
        $ms = $timestampMs % 1000;
        
        $iso = gmdate('Y-m-d\TH:i:s', $timestampSec) . '.' . str_pad($ms, 3, '0', STR_PAD_LEFT) . 'Z';
        
        return [
            'timestamp' => $iso,
            'hidden' => $isHidden,
            'random' => self::encodeRandomToBase64($random)
        ];
    }
}
?>