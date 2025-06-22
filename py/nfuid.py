import os
import time
import random
from datetime import datetime, timezone

class NFUID:
    BASE64_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_'
    BASE64_MAP = {char: i for i, char in enumerate(BASE64_CHARS)}
    
    TIMESTAMP_BITS = 42
    FLAG_BITS = 1
    RANDOM_BITS = 23
    MAX_TIMESTAMP = (1 << TIMESTAMP_BITS) - 1
    MAX_RANDOM = (1 << RANDOM_BITS) - 1
    
    @staticmethod
    def _get_secure_random():
        try:
            # Use os.urandom for cryptographically secure random bytes
            random_bytes = os.urandom(4)
            random_int = int.from_bytes(random_bytes, byteorder='big')
            return random_int & NFUID.MAX_RANDOM
        except:
            # Fallback to regular random (though less secure)
            return random.randint(0, NFUID.MAX_RANDOM)
    
    @staticmethod
    def _stretch_random(random_val):
        v = random_val & NFUID.MAX_RANDOM
        
        v ^= v >> 13
        v *= 0x9e3779b97f4a7c15
        v ^= v >> 12
        v *= 0xbf58476d1ce4e5b9
        v ^= v >> 15
        
        return v & NFUID.MAX_TIMESTAMP
    
    @staticmethod
    def _encode_bits_to_base64(value, bit_count):
        chars = []
        remaining_bits = bit_count
        
        while remaining_bits > 0:
            bits_to_take = min(6, remaining_bits)
            shift = remaining_bits - bits_to_take
            
            index = (value >> shift) & ((1 << bits_to_take) - 1)
            
            if bits_to_take < 6:
                index = index << (6 - bits_to_take)
            
            chars.append(NFUID.BASE64_CHARS[index])
            remaining_bits -= bits_to_take
        
        return ''.join(chars)
    
    @staticmethod
    def _decode_base64_to_bits(string, expected_bit_count):
        value = 0
        total_bits = 0
        
        for char in string:
            if char not in NFUID.BASE64_MAP:
                raise ValueError('Invalid character in string')
            
            char_bits = min(6, expected_bit_count - total_bits)
            char_value = NFUID.BASE64_MAP[char]
            
            if char_bits < 6:
                char_value = char_value >> (6 - char_bits)
            
            value = (value << char_bits) | char_value
            total_bits += char_bits
        
        return value
    
    @staticmethod
    def _encode_random_to_base64(random23):
        return NFUID._encode_bits_to_base64(random23, 23)
    
    @staticmethod
    def generate(hidden=False):
        now = int(time.time() * 1000)  # milliseconds
        timestamp = now & NFUID.MAX_TIMESTAMP
        
        random_val = NFUID._get_secure_random()
        
        flag_bit = 1 if hidden else 0
        
        final_timestamp = timestamp
        
        if hidden:
            stretched = NFUID._stretch_random(random_val)
            final_timestamp = timestamp ^ stretched
            final_timestamp &= NFUID.MAX_TIMESTAMP
        
        combined = (final_timestamp << 24) | (flag_bit << 23) | random_val
        
        return NFUID._encode_bits_to_base64(combined, 66)
    
    @staticmethod
    def parse(id_string):
        value = NFUID._decode_base64_to_bits(id_string, 66)
        
        random_val = value & NFUID.MAX_RANDOM
        flag_bit = (value >> 23) & 1
        timestamp = (value >> 24) & NFUID.MAX_TIMESTAMP
        
        is_hidden = flag_bit == 1
        
        if is_hidden:
            stretched = NFUID._stretch_random(random_val)
            timestamp = timestamp ^ stretched
            timestamp &= NFUID.MAX_TIMESTAMP
        
        return {
            'timestamp': datetime.fromtimestamp(timestamp / 1000.0, tz=timezone.utc),
            'hidden': is_hidden,
            'random': NFUID._encode_random_to_base64(random_val)
        }
