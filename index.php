<?php
// Enhanced REST API for Papua GeoJSON Collections with caching

class PapuaGeoAPI {
    private $base_dir;
    private $geojson_dirs;
    private $structured_dir;
    private $cache_dir;
    private $cache_ttl = 3600; // 1 hour
    
    public function __construct() {
        $this->base_dir = __DIR__;
        $this->geojson_dirs = [
            'province' => $this->base_dir . '/papua_provinces',
            'regency' => $this->base_dir . '/papua_regencies',
            'district' => $this->base_dir . '/papua_districts_detailed',
            'village' => $this->base_dir . '/papua_villages_detailed'
        ];
        $this->structured_dir = $this->base_dir . '/papua_structured_data';
        $this->cache_dir = $this->base_dir . '/cache';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    public function sendError($msg, $code = 404) {
        http_response_code($code);
        echo json_encode(['error' => $msg, 'timestamp' => time()]);
        exit;
    }
    
    public function sendJSON($data, $cache_key = null) {
        header('Content-Type: application/json');
        header('X-API-Version: 2.0');
        
        if ($cache_key) {
            $this->setCache($cache_key, $data);
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    public function sendGeoJSON($file_path, $cache_key = null) {
        if (!file_exists($file_path)) {
            $this->sendError('GeoJSON file not found');
        }
        
        header('Content-Type: application/geo+json');
        header('X-API-Version: 2.0');
        
        if ($cache_key && $cached = $this->getCache($cache_key)) {
            echo $cached;
            return;
        }
        
        $content = file_get_contents($file_path);
        if ($cache_key) {
            $this->setCache($cache_key, $content);
        }
        
        echo $content;
    }
    
    private function getCacheKey($parts) {
        return md5(implode('_', $parts));
    }
    
    private function getCache($key) {
        $file = $this->cache_dir . '/' . $key . '.cache';
        if (file_exists($file) && (time() - filemtime($file)) < $this->cache_ttl) {
            return file_get_contents($file);
        }
        return false;
    }
    
    private function setCache($key, $data) {
        $file = $this->cache_dir . '/' . $key . '.cache';
        file_put_contents($file, is_string($data) ? $data : json_encode($data));
    }
    
    public function listFiles($dir, $ext = 'geojson', $use_cache = true) {
        if (!is_dir($dir)) return [];
        
        $cache_key = $this->getCacheKey(['list', basename($dir), $ext]);
        
        if ($use_cache && $cached = $this->getCache($cache_key)) {
            return json_decode($cached, true);
        }
        
        $files = glob("$dir/*.$ext");
        $result = array_map(function($f) use ($ext) { 
            return basename($f, ".$ext"); 
        }, $files);
        
        if ($use_cache) {
            $this->setCache($cache_key, $result);
        }
        
        return $result;
    }
    
    public function findFilename($dir, $target, $ext = 'geojson') {
        $target_lc = strtolower($target);
        foreach (glob("$dir/*.$ext") as $f) {
            if (strtolower(basename($f, ".$ext")) === $target_lc) {
                return basename($f, ".$ext");
            }
        }
        return null;
    }
    
    public function autocompleteFiles($dir, $prefix, $ext = 'geojson') {
        $cache_key = $this->getCacheKey(['autocomplete', basename($dir), strtolower($prefix)]);
        
        if ($cached = $this->getCache($cache_key)) {
            return json_decode($cached, true);
        }
        
        $prefix_lc = strtolower($prefix);
        $matches = [];
        foreach (glob("$dir/*.$ext") as $f) {
            $name = basename($f, ".$ext");
            if (strpos(strtolower($name), $prefix_lc) === 0) {
                $matches[] = $name;
            }
        }
        
        $this->setCache($cache_key, $matches);
        return $matches;
    }
    
    public function searchFiles($dir, $query, $ext = 'geojson') {
        $cache_key = $this->getCacheKey(['search', basename($dir), strtolower($query)]);
        
        if ($cached = $this->getCache($cache_key)) {
            return json_decode($cached, true);
        }
        
        $query_lc = strtolower($query);
        $matches = [];
        foreach (glob("$dir/*.$ext") as $f) {
            $name = basename($f, ".$ext");
            if (strpos(strtolower($name), $query_lc) !== false) {
                $matches[] = $name;
            }
        }
        
        $this->setCache($cache_key, $matches);
        return $matches;
    }
    
    public function filterByParent($files, $parent) {
        $parent_upper = strtoupper($parent);
        $filtered = [];
        foreach ($files as $file) {
            if (preg_match('/_(.+)$/', $file, $m) && strtoupper($m[1]) === $parent_upper) {
                $filtered[] = $file;
            }
        }
        return $filtered;
    }
    
    public function route($path) {
        $parts = explode('/', trim($path, '/'));
        if (empty($parts[0])) $parts = [''];
        
        switch ($parts[0]) {
            case 'cache':
                return $this->handleCache($parts);
            case 'provinces':
                return $this->handleProvinces($parts);
            case 'regencies':
                return $this->handleRegencies($parts);
            case 'districts':
                return $this->handleDistricts($parts);
            case 'villages':
                return $this->handleVillages($parts);
            case 'files':
                return $this->handleFiles($parts);
            case 'autocomplete':
                return $this->handleAutocomplete($parts);
            case 'search':
                return $this->handleSearch($parts);
            case 'geojson':
                return $this->handleGeoJSON($parts);
            case 'structured':
                return $this->handleStructured($parts);
            default:
                return $this->handleIndex();
        }
    }
    
    private function handleCache($parts) {
        if (!isset($parts[1])) {
            // Cache stats
            $files = glob($this->cache_dir . '/*.cache');
            $stats = [
                'cache_files' => count($files),
                'cache_size' => array_sum(array_map('filesize', $files)),
                'cache_ttl' => $this->cache_ttl
            ];
            $this->sendJSON($stats);
            return;
        }
        
        if ($parts[1] === 'clear') {
            $files = glob($this->cache_dir . '/*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
            $this->sendJSON(['message' => 'Cache cleared', 'files_deleted' => count($files)]);
            return;
        }
        
        $this->sendError('Usage: /cache or /cache/clear');
    }
    
    private function handleProvinces($parts) {
        $cache_key = $this->getCacheKey(['provinces']);
        $files = $this->listFiles($this->geojson_dirs['province']);
        $this->sendJSON($files, $cache_key);
    }
    
    private function handleRegencies($parts) {
        $files = $this->listFiles($this->geojson_dirs['regency']);
        
        if (isset($parts[1])) {
            $filtered = $this->filterByParent($files, $parts[1]);
            $cache_key = $this->getCacheKey(['regencies', strtolower($parts[1])]);
            $this->sendJSON($filtered, $cache_key);
        } else {
            $cache_key = $this->getCacheKey(['regencies']);
            $this->sendJSON($files, $cache_key);
        }
    }
    
    private function handleDistricts($parts) {
        $files = $this->listFiles($this->geojson_dirs['district']);
        
        if (isset($parts[1])) {
            $filtered = $this->filterByParent($files, $parts[1]);
            $cache_key = $this->getCacheKey(['districts', strtolower($parts[1])]);
            $this->sendJSON($filtered, $cache_key);
        } else {
            $cache_key = $this->getCacheKey(['districts']);
            $this->sendJSON($files, $cache_key);
        }
    }
    
    private function handleVillages($parts) {
        $files = $this->listFiles($this->geojson_dirs['village']);
        
        if (isset($parts[1])) {
            $filtered = $this->filterByParent($files, $parts[1]);
            $cache_key = $this->getCacheKey(['villages', strtolower($parts[1])]);
            $this->sendJSON($filtered, $cache_key);
        } else {
            $cache_key = $this->getCacheKey(['villages']);
            $this->sendJSON($files, $cache_key);
        }
    }
    
    private function handleFiles($parts) {
        if (!isset($parts[1]) || !isset($this->geojson_dirs[$parts[1]])) {
            $this->sendError('Usage: /files/{level} where level is: ' . implode(', ', array_keys($this->geojson_dirs)));
        }
        
        $cache_key = $this->getCacheKey(['files', $parts[1]]);
        $files = $this->listFiles($this->geojson_dirs[$parts[1]]);
        $this->sendJSON($files, $cache_key);
    }
    
    private function handleAutocomplete($parts) {
        if (count($parts) < 3 || !isset($this->geojson_dirs[$parts[1]])) {
            $this->sendError('Usage: /autocomplete/{level}/{prefix}');
        }
        
        $matches = $this->autocompleteFiles($this->geojson_dirs[$parts[1]], $parts[2]);
        $this->sendJSON($matches);
    }
    
    private function handleSearch($parts) {
        if (count($parts) < 3 || !isset($this->geojson_dirs[$parts[1]])) {
            $this->sendError('Usage: /search/{level}/{query}');
        }
        
        $matches = $this->searchFiles($this->geojson_dirs[$parts[1]], $parts[2]);
        $this->sendJSON($matches);
    }
    
    private function handleGeoJSON($parts) {
        if (count($parts) < 3) {
            $this->sendError('Usage: /geojson/{level}/{filename}');
        }
        
        $level = $parts[1];
        $filename = $parts[2];
        
        if (!isset($this->geojson_dirs[$level])) {
            $this->sendError('Invalid level: ' . $level);
        }
        
        $actual = $this->findFilename($this->geojson_dirs[$level], $filename);
        if (!$actual) {
            $this->sendError('GeoJSON not found: ' . $filename);
        }
        
        $file_path = $this->geojson_dirs[$level] . '/' . $actual . '.geojson';
        $cache_key = $this->getCacheKey(['geojson', $level, strtolower($actual)]);
        
        $this->sendGeoJSON($file_path, $cache_key);
    }
    
    private function handleStructured($parts) {
        if (!isset($parts[1])) {
            $this->sendError('Usage: /structured/{type}');
        }
        
        $type = $parts[1];
        $file = $this->structured_dir . '/papua_' . $type . '.json';
        
        if (!file_exists($file)) {
            $this->sendError('Structured data not found: ' . $type);
        }
        
        $cache_key = $this->getCacheKey(['structured', $type]);
        
        if ($cached = $this->getCache($cache_key)) {
            header('Content-Type: application/json');
            echo $cached;
            return;
        }
        
        $content = file_get_contents($file);
        $this->setCache($cache_key, $content);
        
        header('Content-Type: application/json');
        echo $content;
    }
    
    private function handleIndex() {
        $endpoints = [
            'Basic Endpoints' => [
                '/provinces' => 'List all provinces (filenames)',
                '/regencies' => 'List all regencies (filenames)',
                '/regencies/{province}' => 'List regencies in a province',
                '/districts' => 'List all districts (filenames)',
                '/districts/{regency}' => 'List districts in a regency',
                '/villages' => 'List all villages (filenames)',
                '/villages/{district}' => 'List villages in a district',
                '/files/{level}' => 'List all filenames for a level (province, regency, district, village)'
            ],
            'Search & Autocomplete' => [
                '/autocomplete/{level}/{prefix}' => 'Autocomplete filenames by prefix (case-insensitive)',
                '/search/{level}/{query}' => 'Search filenames by substring (case-insensitive)'
            ],
            'Data Endpoints' => [
                '/geojson/{level}/{filename}' => 'Fetch GeoJSON by level and filename (case-insensitive)',
                '/structured/administrative_structure' => 'Get full hierarchy JSON',
                '/structured/administrative_summary' => 'Get summary statistics JSON',
                '/structured/provinces_list' => 'Get provinces list JSON',
                '/structured/regencies_list' => 'Get regencies list JSON',
                '/structured/districts_list' => 'Get districts list JSON'
            ],
            'Cache Management' => [
                '/cache' => 'Get cache statistics',
                '/cache/clear' => 'Clear all cached data'
            ]
        ];
        
        $response = [
            'api_version' => '2.0',
            'description' => 'Papua GeoJSON Collections API with caching',
            'endpoints' => $endpoints,
            'usage' => 'Append ?q=ENDPOINT to the URL, e.g. ?q=files/regency',
            'features' => ['caching', 'case-insensitive search', 'autocomplete', 'enhanced error handling']
        ];
        
        $this->sendJSON($response);
    }
}

// Initialize and run API
$api = new PapuaGeoAPI();
$path = isset($_GET['q']) ? $_GET['q'] : '';
$api->route($path);
