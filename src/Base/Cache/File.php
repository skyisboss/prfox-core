<?php 

namespace Prfox\Base\Cache;

class File
{
	public $cacheDir;

	public function __construct()
	{
		$this->cacheDir = app('app')->getRootPath() . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'cache';
		if(!is_dir($this->cacheDir)){
            mkdir($this->cacheDir, 0777, true);
        }
	}

    public function set($name, $data, $expire=0){
    	$cacheFile = $this->getName($name);
    	$expire !== 0 && $expire = intval(time() + $expire);
    	$data = array('data' => $data,'expire' => $expire);
    	$data   = "<?php\n//" . sprintf('%012d', $expire) . "\n exit();?>\n" . serialize($data);
    	file_put_contents($cacheFile, $data);
    }

    public function get($name){
    	$cacheFile = $this->getName($name);
        
    	if(!is_file($cacheFile)){return;}
    	$data = substr(file_get_contents($cacheFile), 32);
    	$cacheData = unserialize($data);
    	unset($data);
    	if(intval($cacheData['expire']) && time() > $cacheData['expire']){
    		unlink($cacheFile);
    		return null;
    	}
    	return $cacheData['data'];
    }

    public function clear(){
    	$files = glob($this->cacheDir .DIRECTORY_SEPARATOR. "*/*.php");
    	foreach ($files as $key => $value) {
    		$dirname = dirname($value);
    		@unlink($value);
    		@rmdir($dirname);
    	}
    	return true;
    }

    public function remove($name){
    	$cacheFile = $this->getName($name);
    	if(!is_file($cacheFile)){return true;}
		unlink($cacheFile);
		return true;
    }

    private function getName($name){
		$name = md5($name);
        $name = substr($name, 0, 3) . DIRECTORY_SEPARATOR . substr($name, 2);
        $name = $this->cacheDir.DIRECTORY_SEPARATOR.$name.'.php';
        $dir = dirname($name);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        return $name;
	}
}