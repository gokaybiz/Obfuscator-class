<?php
require_once "config.php";

class Obfuscator
{
    private const BASE64_REPLACEMENTS = ['=', '/', '+'];
    private const REPLACEMENT_MAP = ['⣿', '⢿', '⡿'];
    private readonly Config $config;

    function __construct(private string $filePath)
    {
        $this->config = new Config(
            ...array_map(fn($_) => $this->generateRandomHex(), range(1, 3)),
            offset: random_int(0, 255),
            salt: bin2hex(random_bytes(32))
        );
    }

    public function encode(): string {
            return pipe(
                $this->getFileContentsAsInterpreted($this->filePath),
                fn($content) => array_map(
                    fn($char) => $this->encodeChar($char),
                    str_split($content)
                ),
                fn($encoded) => $this->wrapInJavaScript($encoded)
            );
        }

        private function encodeChar(string $char): string {
                return pipe(
                    $char,
                    fn($char) => sodium_crypto_generichash($char.$this->config->salt, '', 16).pack('N', ord($char) + $this->config->offset),
                    fn($hash) => msgpack_pack(['d' => $hash]),
                    fn($packed) => base64_encode($packed),
                    fn($base64) => str_replace(
                        self::BASE64_REPLACEMENTS,
                        self::REPLACEMENT_MAP,
                        $base64
                    )
                );
            }

    private function wrapInJavaScript(array $encoded): string {
        $encodedContent = implode(', ', array_map(fn($x) => "'{$x}'", $encoded));
        $replacementMap = pipe(
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

                                    if (!unpacked || !unpacked.d) {
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

    private function minifyJavaScript(string $js): string {
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

    private function getFileContentsAsInterpreted(string $path): string {
        if (!file_exists($path)) {
            throw new RuntimeException("File not found: $path");
        }
        ob_start();
        require $path;
        return ob_get_clean() ?: throw new RuntimeException("Failed to read file: $path");
    }

    private function generateRandomHex(int $length = 2): string {
        return bin2hex(random_bytes($length));
    }
}

function pipe(mixed $value, callable ...$fns): mixed {
    return array_reduce($fns, fn($acc, $fn) => $fn($acc), $value);
}
