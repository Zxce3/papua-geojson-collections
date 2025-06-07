# Papua GeoJSON Collections

Comprehensive geographic boundary data for Papua provinces in Indonesia, organized by administrative hierarchy and provided in GeoJSON format for web mapping applications.

## 📍 Overview

This collection contains complete administrative boundaries for all Papua regions in Indonesia, featuring the latest territorial divisions including the newly established provinces. The data is structured hierarchically from province level down to individual villages, making it suitable for detailed geographic analysis and web mapping applications.

## 🗺️ Coverage

### Papua Provinces (6 Total)
- **Papua** - The original eastern province
- **Papua Barat** - West Papua province  
- **Papua Tengah** - Central Papua (new province)
- **Papua Pegunungan** - Highland Papua (new province)
- **Papua Selatan** - South Papua (new province)
- **Papua Barat Daya** - Southwest Papua (new province)

## 📁 Directory Structure

```
papua-geojson-collections/
├── papua_provinces/              # Province-level boundaries (6 files)
├── papua_regencies/              # Regency-level boundaries (~29 files)  
├── papua_districts_detailed/     # District-level boundaries (~200+ files)
├── papua_villages_detailed/      # Village-level boundaries (~2000+ files)
└── papua_structured_data/        # Administrative hierarchy & statistics
    ├── papua_administrative_structure.json
    ├── papua_administrative_summary.json
    ├── papua_provinces_list.json
    ├── papua_regencies_list.json
    └── papua_districts_list.json
```

## 📊 Administrative Hierarchy

### Level 1: Provinces
Individual GeoJSON files for each Papua province:
- `PAPUA.geojson`
- `PAPUA_BARAT.geojson`
- `PAPUA_TENGAH.geojson`
- `PAPUA_PEGUNUNGAN.geojson`
- `PAPUA_SELATAN.geojson`
- `PAPUA_BARAT_DAYA.geojson`

### Level 2: Regencies (Kabupaten/Kota)
Format: `{regency_name}_{province_name}.geojson`
Examples:
- `Jayapura_PAPUA.geojson`
- `Manokwari_PAPUA_BARAT.geojson`
- `Mimika_PAPUA_TENGAH.geojson`

### Level 3: Districts (Kecamatan)
Format: `{district_name}_{regency_name}.geojson`
Examples:
- `Wamena_Jayawijaya.geojson`
- `Sentani_Jayapura.geojson`
- `Manokwari_Barat_Manokwari.geojson`

### Level 4: Villages (Kelurahan/Desa)
Format: `{village_name}_{district_name}.geojson`
Examples:
- `Wamena_Wamena.geojson`
- `Sentani_Kota_Sentani.geojson`

## 🔧 Data Properties

Each GeoJSON file contains features with the following properties:

### Province Level
```json
{
  "properties": {
    "province": "PAPUA"
  }
}
```

### Regency Level
```json
{
  "properties": {
    "regency": "JAYAPURA",
    "province": "PAPUA"
  }
}
```

### District Level
```json
{
  "properties": {
    "district": "SENTANI",
    "regency": "JAYAPURA", 
    "province": "PAPUA"
  }
}
```

### Village Level
```json
{
  "properties": {
    "village": "SENTANI KOTA",
    "district": "SENTANI",
    "regency": "JAYAPURA",
    "province": "PAPUA"
  }
}
```

## 📋 Structured Data Files

### Administrative Structure (`papua_administrative_structure.json`)
Complete hierarchical organization showing the relationship between all administrative levels:

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
Aggregate counts and statistics:

```json
{
  "total_provinces": 6,
  "total_regencies": 29,
  "total_districts": 200+,
  "total_villages": 2000+,
  "provinces": {
    "PAPUA": {
      "regencies_count": 12,
      "districts_count": 85,
      "villages_count": 800+
    }
  }
}
```

## 🚀 Usage Examples

### Web Mapping with Leaflet
```javascript
// Load province boundaries
fetch('papua_provinces/PAPUA.geojson')
  .then(response => response.json())
  .then(data => {
    L.geoJSON(data).addTo(map);
  });

// Load specific district
fetch('papua_districts_detailed/Wamena_Jayawijaya.geojson')
  .then(response => response.json())
  .then(data => {
    L.geoJSON(data, {
      style: { color: 'blue', weight: 2 }
    }).addTo(map);
  });
```

### Python with GeoPandas
```python
import geopandas as gpd

# Load province data
papua_province = gpd.read_file('papua_provinces/PAPUA.geojson')

# Load all regencies from Papua Barat
regencies = []
for file in glob.glob('papua_regencies/*_PAPUA_BARAT.geojson'):
    regencies.append(gpd.read_file(file))

combined_regencies = gpd.GeoDataFrame(pd.concat(regencies, ignore_index=True))
```

### Administrative Lookup
```python
import json

# Load administrative structure
with open('papua_structured_data/papua_administrative_structure.json') as f:
    admin_data = json.load(f)

# Find all districts in Jayapura regency
jayapura_districts = list(admin_data['PAPUA']['JAYAPURA'].keys())
print(f"Districts in Jayapura: {jayapura_districts}")

# Find all villages in Sentani district
sentani_villages = admin_data['PAPUA']['JAYAPURA']['SENTANI']
print(f"Villages in Sentani: {len(sentani_villages)} villages")
```

## 🎯 Use Cases

### Regional Planning
- Analyze administrative boundaries for development planning
- Calculate areas and distances between regions
- Overlay with other geographic datasets

### Web Applications
- Interactive maps showing Papua administrative divisions
- Location-based services and geocoding
- Regional data visualization dashboards

### Research & Analysis
- Demographic and socioeconomic analysis by administrative unit
- Environmental monitoring and conservation planning
- Transportation and infrastructure planning

## 📏 Data Quality

### Coordinate System
- **CRS**: EPSG:4326 (WGS84 Geographic)
- **Format**: GeoJSON (RFC 7946 compliant)
- **Precision**: Suitable for 1:10,000 scale mapping

### Validation
- All GeoJSON files validated for syntax correctness
- Geometric validity checked (no self-intersections)
- Administrative hierarchy consistency verified
- Filename safety ensured for cross-platform compatibility

## 🔄 Data Updates

This collection is generated from official Indonesian administrative boundary data and reflects the administrative structure as of 2023, including the newest Papua province divisions.

### Source Data
- Original shapefile from [Indonesia Geospasial](https://www.indonesia-geospasial.com/)
- Administrative boundary data (Batas Wilayah Kelurahan/Desa 1:10,000)
- Updated to reflect latest territorial changes


## 📄 License

Geographic data derived from official Indonesian government sources. Please verify licensing requirements for your specific use case.
