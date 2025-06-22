const crypto = require('crypto');

(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.NFUID = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    const BASE64_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
    const BASE64_MAP = {};
    for (let i = 0; i < 64; i++) {
        BASE64_MAP[BASE64_CHARS[i]] = i;
    }

    const TIMESTAMP_BITS = 42;
    const FLAG_BITS = 1;
    const RANDOM_BITS = 23;
    const MAX_TIMESTAMP = (1n << BigInt(TIMESTAMP_BITS)) - 1n;
    const MAX_RANDOM = (1n << BigInt(RANDOM_BITS)) - 1n;
    function getSecureRandom() {
        if (typeof require !== 'undefined') {
            try {
                const bytes = crypto.randomBytes(4);
                return BigInt(bytes.readUInt32BE(0)) & MAX_RANDOM;
            } catch (e) {
                return BigInt(Math.floor(Math.random() * Number(MAX_RANDOM + 1n)));
            }
        } else {
            return BigInt(Math.floor(Math.random() * Number(MAX_RANDOM + 1n)));
        }
    }

    function stretchRandom(random) {
        let v = BigInt(random) & MAX_RANDOM;

        v ^= v >> 13n;
        v *= 0x9e3779b97f4a7c15n;
        v ^= v >> 12n;
        v *= 0xbf58476d1ce4e5b9n;
        v ^= v >> 15n;

        return v & MAX_TIMESTAMP;
    }

    function encodeBitsToBase64(value, bitCount) {
        const chars = [];
        let remainingBits = bitCount;
        
        while (remainingBits > 0) {
            const bitsToTake = Math.min(6, remainingBits);
            const shift = BigInt(remainingBits - bitsToTake);
            
            let index = Number((value >> shift) & ((1n << BigInt(bitsToTake)) - 1n));
            
            if (bitsToTake < 6) {
                index = index << (6 - bitsToTake);
            }
            
            chars.push(BASE64_CHARS[index]);
            remainingBits -= bitsToTake;
        }
        
        return chars.join('');
    }

    function decodeBase64ToBits(str, expectedBitCount) {
        let value = 0n;
        let totalBits = 0;
        
        for (let i = 0; i < str.length; i++) {
            const char = str[i];
            if (!(char in BASE64_MAP)) {
                throw new Error('Invalid character in string');
            }
            
            const charBits = Math.min(6, expectedBitCount - totalBits);
            let charValue = BASE64_MAP[char];
            
            if (charBits < 6) {
                charValue = charValue >> (6 - charBits);
            }
            
            value = (value << BigInt(charBits)) | BigInt(charValue);
            totalBits += charBits;
        }
        
        return value;
    }

    function encode64ToBase64(value) {
        return encodeBitsToBase64(value, 64);
    }

    function decodeBase64To64(str) {
        return decodeBase64ToBits(str, 64);
    }

    function encodeRandomToBase64(random23) {
        return encodeBitsToBase64(random23, 23);
    }

    function generate(hidden = false) {
        const now = BigInt(Date.now());
        const timestamp = now & MAX_TIMESTAMP;
        
        const random = getSecureRandom();
        
        const flagBit = hidden ? 1n : 0n;
        
        let finalTimestamp = timestamp;
        
        if (hidden) {
            const stretched = stretchRandom(random);
            finalTimestamp = timestamp ^ stretched;
            finalTimestamp &= MAX_TIMESTAMP;
        }
        
        const combined = (finalTimestamp << 24n) | (flagBit << 23n) | random;
        
        return encodeBitsToBase64(combined, 66);
    }

    function parse(id) {
        const value = decodeBase64ToBits(id, 66);
        
        const random = value & MAX_RANDOM;
        const flagBit = (value >> 23n) & 1n;
        let timestamp = (value >> 24n) & MAX_TIMESTAMP;
        
        const isHidden = flagBit === 1n;
        
        if (isHidden) {
            const stretched = stretchRandom(random);
            timestamp = timestamp ^ stretched;
            timestamp &= MAX_TIMESTAMP;
        }

        return {
            timestamp: new Date(Number(timestamp)),
            hidden: isHidden,
            random: encodeRandomToBase64(random)
        };
    }

    return {
        generate: generate,
        parse: parse
    };
}));