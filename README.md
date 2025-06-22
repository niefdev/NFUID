# NFUID

[![npm version](https://img.shields.io/npm/v/nfuid)](https://www.npmjs.com/package/nfuid)
[![PyPI version](https://img.shields.io/pypi/v/nfuid)](https://pypi.org/project/nfuid/)
[![Packagist version](https://img.shields.io/packagist/v/niefdev/nfuid)](https://packagist.org/packages/niefdev/nfuid)
[![License](https://img.shields.io/github/license/niefdev/nfuid)](LICENSE)

Minimal 11-character URL-safe unique ID generator with hidden mode and timestamp-based structure.

## Overview

NFUID is a lightweight, cross-platform library for generating and parsing 11-character, URL-safe unique identifiers. Each ID is composed of a 42-bit timestamp (in milliseconds since Unix epoch, UTC), a 1-bit hidden flag, and 23-bit random data, encoded in a custom Base64 alphabet (`ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_`). 

The library supports a "hidden mode" to obfuscate the timestamp, preventing visible timestamp patterns in the ID (note: this is not for security purposes). NFUID is ideal for applications requiring compact, unique IDs, such as logging, tracking, or database keys.

Available in JavaScript, Python, and PHP, NFUID ensures consistent behavior across platforms, with all timestamps in UTC+0 for reliability.

## Features

- **Compact**: Generates 11-character IDs using a URL-safe Base64 alphabet.
- **Timestamp-Based**: Embeds a 42-bit timestamp (milliseconds since 1970-01-01 UTC).
- **Hidden Mode**: Obfuscates timestamp to avoid visible patterns (not for security).
- **Cross-Platform**: Consistent implementation in JavaScript, Python, and PHP.
- **Cryptographic Random**: Uses secure random number generation where available.
- **UTC Consistency**: All timestamps are in UTC+0, ensuring global compatibility.

## Installation

### JavaScript (Node.js or Browser)
Install via npm:
```bash
npm install nfuid
```

### Python
Install via PyPI:
```bash
pip install nfuid
```

### PHP
Install via Composer:
```bash
composer require niefdev/nfuid
```

## Usage

### JavaScript
```javascript
const NFUID = require('nfuid');

// Generate an ID (hidden mode enabled)
const id = NFUID.generate(true);
console.log(`Generated ID: ${id}`); // e.g., "b3kXyZ2pLq8"

// Parse an ID
const parsed = NFUID.parse(id);
console.log(parsed);
// Output: {
//   timestamp: Date object (e.g., 2025-06-22T00:43:00.000Z),
//   hidden: true,
//   random: 'Lq8' (Base64-encoded 23-bit random)
// }

// Ensure UTC output
console.log(parsed.timestamp.toISOString()); // e.g., "2025-06-22T00:43:00.000Z"
```

### Python
```python
from nfuid import NFUID

# Generate an ID (hidden mode enabled)
id = NFUID.generate(hidden=True)
print(f"Generated ID: {id}")  # e.g., "b3kXyZ2pLq8"

# Parse an ID
parsed = NFUID.parse(id)
print(parsed)
# Output: {
#   'timestamp': datetime.datetime(2025, 6, 22, 0, 43, 0, tzinfo=datetime.timezone.utc),
#   'hidden': True,
#   'random': 'Lq8'
# }
```

### PHP
```php
<?php
require 'vendor/autoload.php';

use Niefdev\NFUID;

// Generate an ID (hidden mode enabled)
$id = NFUID::generate(true);
echo "Generated ID: $id\n"; // e.g., "b3kXyZ2pLq8"

// Parse an ID
$parsed = NFUID::parse($id);
print_r($parsed);
// Output: Array (
//   [timestamp] => 2025-06-22T00:43:00.000Z
//   [hidden] => true
//   [random] => Lq8
// )
```

## API

### `generate(hidden = false)`
Generates an 11-character NFUID string.

**Parameters:**
- `hidden` (boolean): If true, obfuscates the timestamp to prevent visible timestamp patterns in the ID (not for security). Default: `false`.

**Returns:** A string representing the NFUID.

### `parse(id)`
Parses an NFUID string into its components.

**Parameters:**
- `id` (string): The NFUID string to parse.

**Returns:**
- **JavaScript**: `{ timestamp: Date, hidden: boolean, random: string }`
- **Python**: `{ 'timestamp': datetime, 'hidden': bool, 'random': str }`
- **PHP**: `[ 'timestamp' => string (ISO 8601), 'hidden' => bool, 'random' => string ]`

**Throws:**
Error if the input string contains invalid Base64 characters or incorrect length.

## Structure of NFUID

- **Total Bits**: 66 bits
- **Timestamp**: 42 bits (milliseconds since 1970-01-01 UTC).
- **Hidden Flag**: 1 bit (indicates if hidden mode is enabled).
- **Random Data**: 23 bits (cryptographically secure where supported).
- **Encoding**: Custom Base64 (`ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_`).
- **Length**: 11 characters (66 bits encoded into Base64).

## Notes

- **Timezone**: All timestamps are in UTC+0. In JavaScript, use `toISOString()` for consistent UTC output. Python uses datetime with `tz=timezone.utc`. PHP outputs ISO 8601 strings with Z suffix.
- **Random Quality**: Uses cryptographic random generation (`crypto.randomBytes` in JavaScript, `os.urandom` in Python, `random_bytes` in PHP) with non-cryptographic fallbacks (`Math.random`, `random.randint`) in unsupported environments. For critical applications, ensure cryptographic random is available.
- **Hidden Mode**: The hidden mode is designed to obscure timestamp patterns in the ID, not to provide security. Use it when you want to avoid predictable timestamp sequences.
- **Interoperability**: IDs generated in one language can be parsed in another, with consistent results.

## Contributing

Contributions are welcome! Please submit issues or pull requests to the GitHub repository.

1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/your-feature`).
3. Commit changes (`git commit -m 'Add your feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

## Issues

Report bugs or request features at https://github.com/niefdev/nfuid/issues.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Author

Developed by **niefdev**.

## Version

Current version: **2.0**

## Repository

Source code: https://github.com/niefdev/nfuid

## Homepage

Learn more: https://github.com/niefdev/nfuid#readme