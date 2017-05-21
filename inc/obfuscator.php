<?php
class Obfucator
{
    private $randomVars = [];
    private $data;

    function __construct($output) {
        $this->randomVars = (object) [
            "Func" => $this->randomString(),
            "Out" => $this->randomString(),
            "Num" => $this->randomString(),
            "Val" => mt_rand(999999, 99999999)
        ];
        $this->data = $output;
    }

    public function encode(){
        $return = '
        <script>var ' . $this->randomVars->Out . ' = ""; ' . str_repeat("   ", rand(5,25)) . ' var ' . $this->randomVars->Num . ' = [';
        foreach(str_split($this->include2var($this->data)) as $x){
            $return .= '"'.base64_encode($this->randomString().(ord($x) + $this->randomVars->Val).$this->randomString()) . '", ';
            if (mt_rand(0, 1)) $return .= "\n".str_repeat(" ", rand(8,24));
            elseif (mt_rand(0, 1)) $return .= str_repeat(" ", rand(3,15));
        }
        $return = rtrim($return, ', ');
        $return .= ']; ' . $this->randomVars->Num . '.forEach(function ' . $this->randomVars->Func . '(value) { ' . $this->randomVars->Out . ' += String.fromCharCode(parseInt(atob(value).replace(/\D/g,\'\')) - ' . $this->randomVars->Val . '); } ); document.write(decodeURIComponent(escape(' . $this->randomVars->Out . '))); </script>
        ';
        return $return;
    }

    private function include2var($file){
        @ob_start();
        require($file);
        return ob_get_clean();
    }

    private function randomString($length = 3) {
        $randomString = '';
        $characters = implode("", array_merge(range('a', 'z'), range('A', 'Z')));
        for ($i = 0; $i < $length; $i++) $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        return $randomString;
    }

}