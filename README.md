# Papua GeoJSON Collections

Complete administrative boundary dataset for Papua provinces in Indonesia with REST API.

## Overview

- **8,205 GeoJSON files** covering all Papua administrative levels
- **REST API v2.0** with caching and search capabilities
- **RFC 7946 compliant** GeoJSON format
- **Production ready** with performance optimizations

## Data Structure

```
Level 1: 6 provinces     → 6 files
Level 2: 42 regencies    → 42 files  
Level 3: 785 districts   → 785 files
Level 4: 7,372 villages  → 7,372 files
```

### Directory Layout
```
papua-geojson-collections/
├── papua_provinces/              # 6 province files
├── papua_regencies/              # 42 regency files
├── papua_districts_detailed/     # 785 district files
├── papua_villages_detailed/      # 7,372 village files
├── papua_structured_data/        # JSON metadata
├── index.php                     # REST API
├── README.md                     # This file
└── API_DOCS.md                   # Complete API documentation
```

## Quick Start

### Direct File Access
```bash
# Download a province boundary
curl -O http://api.example.com/papua_provinces/PAPUA.geojson

# Load with Python
import geopandas as gpd
gdf = gpd.read_file('papua_provinces/PAPUA.geojson')
```

### REST API Usage
```bash
# List all provinces
curl "http://api.example.com/?q=provinces"

# Search regencies containing "jay"
curl "http://api.example.com/?q=search/regency/jay"

# Get GeoJSON data
curl "http://api.example.com/?q=geojson/province/PAPUA"
```

## API Endpoints

| Endpoint | Description |
|----------|-------------|
| `/provinces` | List province files |
| `/regencies/{province?}` | List regency files |
| `/districts/{regency?}` | List district files |
| `/villages/{district?}` | List village files |
| `/search/{level}/{query}` | Search by substring |
| `/autocomplete/{level}/{prefix}` | Autocomplete by prefix |
| `/geojson/{level}/{filename}` | Get GeoJSON data |
| `/structured/{type}` | Get JSON metadata |
| `/cache` | Cache statistics |

**📖 Complete API documentation:** See [API_DOCS.md](API_DOCS.md) for detailed endpoint specifications, examples, and troubleshooting.

## File Naming

- **Province:** `PAPUA.geojson`
- **Regency:** `Jayapura_PAPUA.geojson`
- **District:** `Sentani_Jayapura.geojson`
- **Village:** `Sentani_Kota_Sentani.geojson`

## Data Schema

```json
{
  "type": "Feature",
  "properties": {
    "village": "SENTANI KOTA",
    "district": "SENTANI",
    "regency": "JAYAPURA",
    "province": "PAPUA"
  },
  "geometry": { /* ... */ }
}
```


## Installation

1. **Clone repository:**
   ```bash
   git clone https://github.com/zxce3/papua-geojson-collections.git
   cd papua-geojson-collections
   ```

2. **Start API server:**
   ```bash
   php -S 0.0.0.0:8080
   ```

3. **Test API:**
   ```bash
   curl http://localhost:8080/?q=provinces
   ```

## Features

- ✅ **Case-insensitive search** - Find files regardless of case
- ✅ **Autocomplete** - Prefix-based filename matching
- ✅ **Caching** - 1-hour TTL for improved performance
- ✅ **Error handling** - Detailed error messages
- ✅ **Validation** - GeoJSON format compliance

## Data Quality

- **Coordinate System:** EPSG:4326 (WGS84)
- **Format:** GeoJSON (RFC 7946)
- **Precision:** 6 decimal places
- **Validation:** No self-intersections
- **File sizes:** 1KB - 12MB per file

## Source

Data from official Indonesian government boundaries (Indonesia Geospasial), updated 2023 including new Papua provinces.

## License

Geographic data derived from official Indonesian government sources. Verify licensing for your use case.

## Usage Examples

### JavaScript
```javascript
// Fetch province data
const response = await fetch('http://api.example.com/?q=geojson/province/PAPUA');
const geojson = await response.json();

// Add to Leaflet map
L.geoJSON(geojson).addTo(map);
```

### Python
```python
import requests
import geopandas as gpd

# Get regency list
regencies = requests.get('http://api.example.com/?q=regencies/PAPUA').json()

# Load multiple regencies
gdfs = []
for regency in regencies:
    url = f'http://api.example.com/?q=geojson/regency/{regency}'
    gdf = gpd.read_file(url)
    gdfs.append(gdf)

combined = gpd.GeoDataFrame(pd.concat(gdfs, ignore_index=True))
```

### Shell
```bash
# Validate all province files
for file in papua_provinces/*.geojson; do
    geojsonlint "$file" && echo "✓ $file" || echo "✗ $file"
done

# Convert to Shapefile
ogr2ogr -f "ESRI Shapefile" output.shp papua_provinces/PAPUA.geojson
```

## Documentation

- **[API_DOCS.md](API_DOCS.md)** - Complete API reference with examples
- **[README.md](README.md)** - This overview document

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test the API functionality
5. Submit a pull request
