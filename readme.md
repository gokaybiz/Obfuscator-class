# Obfuscator

## Overview
Obfuscator is a PHP package designed to obfuscate frontend code by encoding it using cryptographic hashing and msgpack encoding. This helps in protecting sensitive HTML code from direct human readability (At least for non-developers).

## Features
- Uses `sodium_crypto_generichash` for hashing.
- Encodes data with `msgpack`.
- Implements a functional programming pipeline for transformations.
- Supports automatic decoding in the browser with JavaScript.
- Easily integrates into PHP projects via Composer.

### Basic Example
Check example [folder](https://github.com/gokaybiz/Obfuscator-class/tree/master/example/)

## Contributing
Feel free to submit pull requests or report issues.

## License
This project is licensed under the GPL-3.0-or-later License.

## Examples (Input->Output)
#### Input:
```html
<div class="container">
	<div class="post-heading-center">
		<h1>TEST PAGE</h1>
	</div>
	<div class="row">
		<div class="col-md-6 col-lg-offset-1">
			<div class="margin-bottom20">
				<p>LETS TEST OBFUSCATION</p>
			</div>
		</div>
	</div>
</div>
```
#### Output:
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/msgpack5/5.3.2/msgpack5.min.js"></script><script> (() => { const REPLACEMENTS = {"\u28ff":"=","\u28bf":"\/","\u287f":"+"}; const data = ['tHtkqMPZlYC7WldAytDk3OIAAABb', 'tHtkqMPZlYC7WldAytDk3OIAAABb', [...truncated...], 'tBWpK9Uz81bidUYwnwMvicgAAACe', 'tJKy6ITq0ooQQEu3qoZWFN8AAACq', 'tOblp7sDt9WuUW6Mp9gQO0sAAACp', 'tLSEyW67⢿ei⡿t1N4UJGNJasAAACv', 'tAcpXVD3NsKZzoD4ZWs82fsAAACc', 'tED0vcP6eoLY5klLCiCOCXkAAACk', 'tOblp7sDt9WuUW6Mp9gQO0sAAACp', 'tAhS4SY3DxVGeDKGk6YBArUAAACg', 'tI8i9EpS4YKvedq10gPEaZ8AAACt', 'tAEgOEnsOg1⢿uBV6⢿g7tmJcAAABd', 'tKQKiDS1V28fcqlHFRwKL6sAAAB5', 'tEH9bqDQpmuIsgEUCYg7iQcAAACf', 'tED0vcP6eoLY5klLCiCOCXkAAACk', 'tNOKP1FjfcJGSh9⢿x85sthUAAACx', 'tHtkqMPZlYC7WldAytDk3OIAAABb', 'tBWpK9Uz81bidUYwnwMvicgAAACe', 'tA7⡿jf9AwGm⢿Q⢿kLsi⡿qp2UAAACn', 'tAcpXVD3NsKZzoD4ZWs82fsAAACc', 'tIadn3⢿dBVwGEo2tSrD3QScAAACu', 'tIadn3⢿dBVwGEo2tSrD3QScAAACu', [...truncated...], 'tKQKiDS1V28fcqlHFRwKL6sAAAB5', 'tNHOm9VQWq4azvVlwk1oUxsAAABF', 'tCrf2Xp1ndBNpqlEZemgBF0AAABE', 'tCrf2Xp1ndBNpqlEZemgBF0AAABE', 'tGUpEpAys3jKbbbJCUhG34YAAAB3', 'tF6s9dYxZP2vGi⡿krPh51QcAAABq', 'tEH9bqDQpmuIsgEUCYg7iQcAAACf', 'tED0vcP6eoLY5klLCiCOCXkAAACk', 'tNOKP1FjfcJGSh9⢿x85sthUAAACx', 'tKQKiDS1V28fcqlHFRwKL6sAAAB5', 'tNHOm9VQWq4azvVlwk1oUxsAAABF']; const base64Replacements = (str) => { return str.split('').map(char => REPLACEMENTS[char] ?? char).join(''); }; const base64ToUint8Array = (base64) => { let binaryString = atob(base64); let len = binaryString.length; let bytes = new Uint8Array(len); for (let i = 0; i < len; i++) { bytes[i] = binaryString.charCodeAt(i); } return bytes; }; function decodeObfuscated(str) { let base64 = base64Replacements(str); let packed = base64ToUint8Array(base64); let unpacked; try { unpacked = msgpack5().decode(packed); } catch (error) { console.error("msgpack decode error:", error); return "?"; } if (!unpacked) { console.warn("Invalid unpacked data:", unpacked); return "?"; } let hashPart = packed.slice(0, 16); let charCodePart = packed.slice(-4); let charCode = new DataView(charCodePart.buffer).getUint32(0, false)-59; return String.fromCharCode(charCode); } let decodedText = data.map(decodeObfuscated).join(""); document.body.insertAdjacentHTML('beforeend', decodedText); })(); </script>
```
