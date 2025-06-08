# Papua GeoJSON Collections API Documentation

REST API v2.0 for accessing Papua administrative boundary data with caching and search capabilities.

## Base URL

```
http://your-domain.com/index.php
```

All endpoints use the query parameter `q` to specify the route:
```
http://your-domain.com/index.php?q=ENDPOINT
```

## Authentication

No authentication required. This is a public read-only API.

## Rate Limiting

No rate limiting currently implemented. Consider implementing for production use.

## Response Format

### Success Response
```json
{
  "data": [...],
  "timestamp": 1234567890
}
```

### Error Response
```json
{
  "error": "Error message description",
  "timestamp": 1234567890
}
```

## Headers

All responses include:
- `Content-Type: application/json` or `application/geo+json`
- `X-API-Version: 2.0`

## Endpoints

### 1. List Provinces

**GET** `/?q=provinces`

Returns array of all province filenames.

```bash
curl "http://api.example.com/?q=provinces"
```

**Response:**
```json
[
  "PAPUA",
  "PAPUA_BARAT",
  "PAPUA_TENGAH",
  "PAPUA_PEGUNUNGAN",
  "PAPUA_SELATAN",
  "PAPUA_BARAT_DAYA"
]
```

### 2. List Regencies

**GET** `/?q=regencies`  
**GET** `/?q=regencies/{province}`

Returns array of regency filenames, optionally filtered by province.

```bash
# All regencies
curl "http://api.example.com/?q=regencies"

# Regencies in Papua province
curl "http://api.example.com/?q=regencies/PAPUA"
```

**Response:**
```json
[
  "Jayapura_PAPUA",
  "Merauke_PAPUA",
  "Biak_Numfor_PAPUA"
]
```

### 3. List Districts

**GET** `/?q=districts`  
**GET** `/?q=districts/{regency}`

Returns array of district filenames, optionally filtered by regency.

```bash
# All districts
curl "http://api.example.com/?q=districts"

# Districts in Jayapura regency
curl "http://api.example.com/?q=districts/Jayapura"
```

**Response:**
```json
[
  "Sentani_Jayapura",
  "Heram_Jayapura",
  "Kemtuk_Gresi_Jayapura"
]
```

### 4. List Villages

**GET** `/?q=villages`  
**GET** `/?q=villages/{district}`

Returns array of village filenames, optionally filtered by district.

```bash
# All villages
curl "http://api.example.com/?q=villages"

# Villages in Sentani district
curl "http://api.example.com/?q=villages/Sentani"
```

**Response:**
```json
[
  "Sentani_Kota_Sentani",
  "Doyo_Baru_Sentani",
  "Hobong_Sentani"
]
```

### 5. List Files by Level

**GET** `/?q=files/{level}`

Returns array of all filenames for a specific administrative level.

**Parameters:**
- `level`: One of `province`, `regency`, `district`, `village`

```bash
curl "http://api.example.com/?q=files/regency"
```

**Response:**
```json
[
  "Jayapura_PAPUA",
  "Merauke_PAPUA",
  "Manokwari_PAPUA_BARAT"
]
```

### 6. Search Files

**GET** `/?q=search/{level}/{query}`

Search filenames by substring (case-insensitive).

**Parameters:**
- `level`: Administrative level (`province`, `regency`, `district`, `village`)
- `query`: Search query string

```bash
curl "http://api.example.com/?q=search/regency/jay"
```

**Response:**
```json
[
  "Jayapura_PAPUA",
  "Jayawijaya_PAPUA"
]
```

### 7. Autocomplete Files

**GET** `/?q=autocomplete/{level}/{prefix}`

Autocomplete filenames by prefix (case-insensitive).

**Parameters:**
- `level`: Administrative level
- `prefix`: Prefix string

```bash
curl "http://api.example.com/?q=autocomplete/regency/Jay"
```

**Response:**
```json
[
  "Jayapura_PAPUA",
  "Jayawijaya_PAPUA"
]
```

### 8. Get GeoJSON Data

**GET** `/?q=geojson/{level}/{filename}`

Returns GeoJSON data for a specific file (case-insensitive filename matching).

**Parameters:**
- `level`: Administrative level
- `filename`: Filename without .geojson extension

```bash
curl "http://api.example.com/?q=geojson/province/PAPUA"
```

**Response:**
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "province": "PAPUA"
      },
      "geometry": {
        "type": "Polygon",
        "coordinates": [...]
      }
    }
  ]
}
```

### 9. Get Structured Data

**GET** `/?q=structured/{type}`

Returns structured JSON metadata.

**Available types:**
- `administrative_structure` - Complete hierarchy
- `administrative_summary` - Statistics summary
- `provinces_list` - Province list
- `regencies_list` - Regency list
- `districts_list` - District list

```bash
curl "http://api.example.com/?q=structured/administrative_summary"
```

**Response:**
```json
{
  "total_provinces": 6,
  "total_regencies": 42,
  "total_districts": 785,
  "total_villages": 7372,
  "provinces": {
    "PAPUA": {
      "regencies_count": 8,
      "districts_count": 41,
      "villages_count": 412
    }
  }
}
```

### 10. Cache Management

**GET** `/?q=cache`

Returns cache statistics.

```bash
curl "http://api.example.com/?q=cache"
```

**Response:**
```json
{
  "cache_files": 145,
  "cache_size": 2048576,
  "cache_ttl": 3600
}
```

**GET** `/?q=cache/clear`

Clears all cached data.

```bash
curl "http://api.example.com/?q=cache/clear"
```

**Response:**
```json
{
  "message": "Cache cleared",
  "files_deleted": 145
}
```

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 404 | Resource not found |
| 400 | Bad request (invalid parameters) |
| 500 | Internal server error |

## Common Error Messages

```json
{"error": "Usage: /files/{level} where level is: province, regency, district, village"}
{"error": "GeoJSON not found: filename"}
{"error": "Invalid level: invalidlevel"}
{"error": "Structured data not found: invalidtype"}
```

## Performance

### Caching
- Cache TTL: 1 hour (3600 seconds)
- Cache storage: File-based in `/cache` directory
- Cache keys: MD5 hashed from request parameters

### Response Times
| Endpoint Type | Cache Hit | Cache Miss |
|---------------|-----------|------------|
| File listings | ~5ms | ~50ms |
| Search/Autocomplete | ~5ms | ~100ms |
| GeoJSON data | ~10ms | ~200ms |
| Structured data | ~5ms | ~20ms |

## Usage Examples

### JavaScript
```javascript
class PapuaAPI {
  constructor(baseUrl) {
    this.baseUrl = baseUrl;
  }
  
  async get(endpoint) {
    const response = await fetch(`${this.baseUrl}?q=${endpoint}`);
    return response.json();
  }
  
  async getGeoJSON(level, filename) {
    return this.get(`geojson/${level}/${filename}`);
  }
  
  async search(level, query) {
    return this.get(`search/${level}/${query}`);
  }
}

// Usage
const api = new PapuaAPI('http://api.example.com/');
const provinces = await api.get('provinces');
const papua = await api.getGeoJSON('province', 'PAPUA');
```

### Python
```python
import requests

class PapuaAPI:
    def __init__(self, base_url):
        self.base_url = base_url
    
    def get(self, endpoint):
        response = requests.get(f"{self.base_url}?q={endpoint}")
        return response.json()
    
    def get_geojson(self, level, filename):
        return self.get(f"geojson/{level}/{filename}")
    
    def search(self, level, query):
        return self.get(f"search/{level}/{query}")

# Usage
api = PapuaAPI('http://api.example.com/')
provinces = api.get('provinces')
papua = api.get_geojson('province', 'PAPUA')
```

### PHP
```php
class PapuaAPI {
    private $baseUrl;
    
    public function __construct($baseUrl) {
        $this->baseUrl = $baseUrl;
    }
    
    public function get($endpoint) {
        $url = $this->baseUrl . '?q=' . urlencode($endpoint);
        return json_decode(file_get_contents($url), true);
    }
    
    public function getGeoJSON($level, $filename) {
        return $this->get("geojson/{$level}/{$filename}");
    }
    
    public function search($level, $query) {
        return $this->get("search/{$level}/{$query}");
    }
}

// Usage
$api = new PapuaAPI('http://api.example.com/');
$provinces = $api->get('provinces');
$papua = $api->getGeoJSON('province', 'PAPUA');
```

## File Naming Conventions

### Province Files
- Pattern: `{PROVINCE_NAME}.geojson`
- Example: `PAPUA.geojson`

### Regency Files
- Pattern: `{Regency_Name}_{PROVINCE_NAME}.geojson`
- Example: `Jayapura_PAPUA.geojson`

### District Files
- Pattern: `{District_Name}_{Regency_Name}.geojson`
- Example: `Sentani_Jayapura.geojson`

### Village Files
- Pattern: `{Village_Name}_{District_Name}.geojson`
- Example: `Sentani_Kota_Sentani.geojson`

## Data Schema

All GeoJSON files follow this property schema:

```json
{
  "type": "Feature",
  "properties": {
    "village": "SENTANI KOTA",      // Only in village level
    "district": "SENTANI",          // District level and below
    "regency": "JAYAPURA",          // Regency level and below
    "province": "PAPUA"             // All levels
  },
  "geometry": {
    "type": "Polygon",              // or MultiPolygon
    "coordinates": [...]
  }
}
```

## Troubleshooting

### Common Issues

1. **File not found errors**
   - Check filename case sensitivity
   - Use `/files/{level}` to list available files
   - Verify the administrative level is correct

2. **Slow response times**
   - Check cache status with `/cache`
   - Clear cache if needed with `/cache/clear`
   - Consider server resources

3. **Invalid parameters**
   - Verify endpoint syntax
   - Check available levels: `province`, `regency`, `district`, `village`
   - Use root endpoint `/?q=` for API documentation

### Debug Steps

1. Test API availability:
   ```bash
   curl "http://api.example.com/"
   ```

2. Check specific endpoint:
   ```bash
   curl -v "http://api.example.com/?q=provinces"
   ```

3. Verify file existence:
   ```bash
   curl "http://api.example.com/?q=files/province"
   ```

4. Check cache status:
   ```bash
   curl "http://api.example.com/?q=cache"
   ```

## Changelog

### v2.0
- Added caching system
- Case-insensitive search and filename matching
- Enhanced error handling
- Added autocomplete functionality
- Improved performance
- Added cache management endpoints

### v1.0
- Initial API release
- Basic CRUD operations
- File listing capabilities
- GeoJSON data retrieval
