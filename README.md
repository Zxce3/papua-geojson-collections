# Papua GeoJSON Collections

Comprehensive, machine-readable geographic boundary data for Papua provinces in Indonesia, organized by administrative hierarchy and provided in GeoJSON format for spatial analysis, GIS, and web mapping applications.

## 📍 Overview

This repository provides authoritative, up-to-date administrative boundaries for all Papua regions in Indonesia, including the latest province splits. Data is structured hierarchically (province → regency → district → village) and is suitable for programmatic access, geospatial analytics, and integration with mapping frameworks.

## 🗺️ Coverage

### Papua Provinces (6 Total)
- **Papua** - The original eastern province
- **Papua Barat** - West Papua province  
- **Papua Tengah** - Central Papua (new province)
- **Papua Pegunungan** - Highland Papua (new province)
- **Papua Selatan** - South Papua (new province)
- **Papua Barat Daya** - Southwest Papua (new province)

#### Regency Count
- **Total Regencies:** 42  
  *(as of current data files in `papua_regencies/`)*

## 📁 Directory Structure

```
papua-geojson-collections/
├── papua_provinces/              # Province-level boundaries (6 files, GeoJSON)
├── papua_regencies/              # Regency-level boundaries (42 files, GeoJSON)
├── papua_districts_detailed/     # District-level boundaries (785 files, GeoJSON)
├── papua_villages_detailed/      # Village-level boundaries (7,372 files, GeoJSON)
└── papua_structured_data/        # Administrative hierarchy & statistics (JSON)
    ├── papua_administrative_structure.json
    ├── papua_administrative_summary.json
    ├── papua_provinces_list.json
    ├── papua_regencies_list.json
    └── papua_districts_list.json
```

- **GeoJSON files** are RFC 7946 compliant, UTF-8 encoded, and use WGS84 (EPSG:4326).
- **Structured JSON** files provide fast lookup and programmatic access to the hierarchy.

## 📊 Administrative Hierarchy

### Level 1: Provinces
Each province is a single GeoJSON file:
- `PAPUA.geojson`
- `PAPUA_BARAT.geojson`
- `PAPUA_TENGAH.geojson`
- `PAPUA_PEGUNUNGAN.geojson`
- `PAPUA_SELATAN.geojson`
- `PAPUA_BARAT_DAYA.geojson`

### Level 2: Regencies (Kabupaten/Kota)
Format: `{regency_name}_{province_name}.geojson`
- Example: `Jayapura_PAPUA.geojson`, `Manokwari_PAPUA_BARAT.geojson`

### Level 3: Districts (Kecamatan)
Format: `{district_name}_{regency_name}.geojson`
- Example: `Wamena_Jayawijaya.geojson`, `Sentani_Jayapura.geojson`

### Level 4: Villages (Kelurahan/Desa)
Format: `{village_name}_{district_name}.geojson`
- Example: `Wamena_Wamena.geojson`, `Sentani_Kota_Sentani.geojson`

## 🔧 Data Schema

All GeoJSON files use a consistent property schema for interoperability with GIS tools and code.

### Province Level
```json
{
  "type": "Feature",
  "properties": {
    "province": "PAPUA"
  },
  "geometry": { /* ... */ }
}
```

### Regency Level
```json
{
  "type": "Feature",
  "properties": {
    "regency": "JAYAPURA",
    "province": "PAPUA"
  },
  "geometry": { /* ... */ }
}
```

### District Level
```json
{
  "type": "Feature",
  "properties": {
    "district": "SENTANI",
    "regency": "JAYAPURA", 
    "province": "PAPUA"
  },
  "geometry": { /* ... */ }
}
```

### Village Level
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

## 📋 Structured Data Files

### Administrative Structure (`papua_administrative_structure.json`)
Hierarchical JSON for fast programmatic lookup:

```json
{
  "PAPUA": {
    "JAYAPURA": {
      "SENTANI": ["SENTANI KOTA", "DOYO BARU", "..."],
      "WAMENA": ["WAMENA", "HUBIKIAK", "..."]
    }
  }
}
```

### Summary Statistics (`papua_administrative_summary.json`)
Aggregate counts for automation and validation:

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

## 🚀 Usage Examples

### JavaScript (Leaflet, Mapbox GL, etc.)
```javascript
// Load province boundaries (Leaflet)
fetch('papua_provinces/PAPUA.geojson')
  .then(r => r.json())
  .then(data => L.geoJSON(data).addTo(map));

// Load a district with custom style
fetch('papua_districts_detailed/Wamena_Jayawijaya.geojson')
  .then(r => r.json())
  .then(data => L.geoJSON(data, { style: { color: 'blue', weight: 2 } }).addTo(map));
```

### Python (GeoPandas, Pandas)
```python
import geopandas as gpd
import glob
import pandas as pd

# Load province data
papua_province = gpd.read_file('papua_provinces/PAPUA.geojson')

# Load all regencies from Papua Barat
regencies = [gpd.read_file(f) for f in glob.glob('papua_regencies/*_PAPUA_BARAT.geojson')]
combined_regencies = gpd.GeoDataFrame(pd.concat(regencies, ignore_index=True))
```

### Administrative Lookup (Python)
```python
import json

with open('papua_structured_data/papua_administrative_structure.json') as f:
    admin_data = json.load(f)

# List all districts in Jayapura regency
jayapura_districts = list(admin_data['PAPUA']['JAYAPURA'].keys())

# List all villages in Sentani district
sentani_villages = admin_data['PAPUA']['JAYAPURA']['SENTANI']
```

### CLI & Automation

- **Validate GeoJSON:**  
  `geojsonlint papua_provinces/PAPUA.geojson`
- **Convert to Shapefile:**  
  `ogr2ogr -f "ESRI Shapefile" output.shp papua_provinces/PAPUA.geojson`
- **Batch process:**  
  Use shell scripts or Python to iterate over files for ETL or analytics.

## 🎯 Use Cases

- Regional planning and spatial analysis
- Web mapping and interactive dashboards
- Geocoding and location-based services
- Demographic, environmental, and infrastructure research
- Automated data pipelines and GIS integration

## 📏 Data Quality & Standards

- **CRS:** EPSG:4326 (WGS84)
- **Format:** GeoJSON (RFC 7946)
- **Precision:** Suitable for 1:10,000 scale mapping
- **Validation:** All files checked for GeoJSON compliance and geometric validity
- **Naming:** All filenames are ASCII-safe and cross-platform compatible

## 🔄 Data Updates & Provenance

- Data reflects the administrative structure as of 2023, including all new provinces.
- Source: Official Indonesian government shapefiles ([Indonesia Geospasial](https://www.indonesia-geospasial.com/)), Batas Wilayah Kelurahan/Desa 1:10,000.
- Updates are versioned and changelogged for reproducibility.

## 📄 License

Geographic data derived from official Indonesian government sources. Please verify licensing requirements for your specific use case.
