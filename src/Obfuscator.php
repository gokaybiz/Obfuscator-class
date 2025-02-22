<?php

namespace Gokaybiz\Obfuscator;

use Gokaybiz\Obfuscator\Config;
use Gokaybiz\Obfuscator\Util;
use MessagePack\MessagePack;
use RuntimeException;

class Obfuscator
{
    private const BASE64_REPLACEMENTS = ['=', '/', '+'];
    private const REPLACEMENT_MAP = ['⣿', '⢿', '⡿'];
    private readonly Config $config;

    /**
     * Creates a new Obfuscator instance.
     *
     * @param string $filePath The file path.
     */
    function __construct(private string $filePath)
    {
        $this->config = new Config(
            ...array_map(fn($_) => $this->generateRandomHex(), range(1, 3)),
            offset: random_int(0, 255),
            salt: bin2hex(random_bytes(32))
        );
    }

    /**
     * Encodes the file contents using the obfuscation algorithm.
     *
     * @return string The encoded file contents.
     */
    public function encode(): string
    {
        return Util::pipe(
            $this->getFileContentsAsInterpreted($this->filePath),
            fn($content) => array_map(
                fn($char) => $this->encodeChar($char),
                str_split($content)
            ),
            fn($encoded) => $this->wrapInJavaScript($encoded)
        );
    }

    /**
     * Encodes a single character using the obfuscation algorithm.
     *
     * @param string $char The character to encode.
     * @return string The encoded character.
     */
    private function encodeChar(string $char): string
    {
        return Util::pipe(
            sodium_crypto_generichash($char . $this->config->salt, '', 16),
            fn($genericHash) => $genericHash . pack('N', ord($char) + $this->config->offset),
            fn($hash) => MessagePack::pack($hash),
            fn($packed) => base64_encode($packed),
            fn($base64) => str_replace(
                self::BASE64_REPLACEMENTS,
                self::REPLACEMENT_MAP,
                $base64
            )
        );
    }


    /**
     * Wraps the encoded content in a JavaScript function.
     *
     * @param array $encoded The encoded content.
     * @return string The JavaScript function.
     */
    private function wrapInJavaScript(array $encoded): string
    {
        $encodedContent = implode(', ', array_map(fn($x) => "'{$x}'", $encoded));
        $replacementMap = Util::pipe(
            array_combine(self::REPLACEMENT_MAP, self::BASE64_REPLACEMENTS),
            fn($map) => json_encode($map)
        );

        $msgpackCdn = <<<MSGPACK_CDN
        <script src="https://cdnjs.cloudflare.com/ajax/libs/msgpack5/5.3.2/msgpack5.min.js"></script>
MSGPACK_CDN;

        $js = <<<JS
        <script>
                (() => {
                    const REPLACEMENTS = {$replacementMap};
                    const data = [{$encodedContent}];

                    const base64Replacements = (str) => {
                        return str.split('').map(char => REPLACEMENTS[char] ?? char).join('');
                    };

                    const base64ToUint8Array = (base64) => {
                        let binaryString = atob(base64);
                        let len = binaryString.length;
                        let bytes = new Uint8Array(len);
                        for (let i = 0; i < len; i++) {
                            bytes[i] = binaryString.charCodeAt(i);
                        }
                        return bytes;
                    };

                    function decodeObfuscated(str) {
                        let base64 = base64Replacements(str);
                        let packed = base64ToUint8Array(base64);

                        let unpacked;
                        try {
                            unpacked = msgpack5().decode(packed);
                        } catch (error) {
                            console.error("msgpack decode error:", error);
                            return "?";
                        }

                        if (!unpacked) {
                            console.warn("Invalid unpacked data:", unpacked);
                            return "?";
                        }


                        let hashPart = packed.slice(0, 16); // First 16 bytes (hash)
                        let charCodePart = packed.slice(-4); // Last 4 bytes (ASCII code)

                        let charCode = new DataView(charCodePart.buffer).getUint32(0, false)-{$this->config->offset}; // Big-endian

                        return String.fromCharCode(charCode);
                    }

                    let decodedText = data.map(decodeObfuscated).join("");
                    document.body.insertAdjacentHTML('beforeend', decodedText);
                  })();
                </script>
        JS;
        return $msgpackCdn . $this->minifyJavaScript($js);
    }

    /**
     * Minifies the JavaScript code.
     *
     * @param string $js The JavaScript code.
     * @return string The minified JavaScript code.
     */
    private function minifyJavaScript(string $js): string
    {
        return preg_replace(
            [
                '#/\*.*?\*/#s',    // Remove multi-line comments
                '#//.*?$#m',       // Remove single-line comments
                '/\s{2,}/',        // Collapse multiple spaces
                '/\n\s*/',         // Remove newlines before/after spaces
            ],
            ['', '', ' ', ''],
            trim($js)
        );
    }

    /**
     * Gets the file contents as interpreted.
     *
     * @param string $path The file path.
     * @return string The file contents as interpreted.
     */
    private function getFileContentsAsInterpreted(string $path): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException("File not found: $path");
        }
        ob_start();
        require $path;
        return ob_get_clean() ?: throw new RuntimeException("Failed to read file: $path");
    }

    /**
     * Generates a random hexadecimal string.
     *
     * @param int $length The length of the hexadecimal string.
     * @return string The generated hexadecimal string.
     */
    private function generateRandomHex(int $length = 2): string
    {
        return Util::pipe(
            random_bytes($length),
            fn($randomBytes) => bin2hex($randomBytes),
        );
    }
}
