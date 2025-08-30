<?php

class TerrainMapGenerator {
    
    // Terrain types
    const WATER = 0;
    const PLAINS = 1;
    const FOREST = 2;
    const MOUNTAINS = 3;
    const DESERT = 4;
    const SWAMP = 5;
    
    // Terrain symbols for display
    private $terrainSymbols = [
        self::WATER => '~',
        self::PLAINS => '.',
        self::FOREST => 'T',
        self::MOUNTAINS => '^',
        self::DESERT => 's',
        self::SWAMP => 'M'
    ];
    
    // Terrain colors for HTML output
    private $terrainColors = [
        self::WATER => '#4A90E2',
        self::PLAINS => '#90EE90',
        self::FOREST => '#228B22',
        self::MOUNTAINS => '#8B7355',
        self::DESERT => '#F4A460',
        self::SWAMP => '#556B2F'
    ];
    
    private $width;
    private $height;
    private $map;
    
    public function __construct($width = 50, $height = 50) {
        $this->width = $width;
        $this->height = $height;
        $this->map = [];
    }
    
    /**
     * Generate a random terrain map using noise-based generation
     */
    public function generateMap($seed = null) {
        if ($seed !== null) {
            srand($seed);
        }
        
        // Initialize map with water
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $this->map[$y][$x] = self::WATER;
            }
        }
        
        // Generate landmasses using multiple noise layers
        $this->generateLandmasses();
        $this->addTerrainVariation();
        $this->smoothTerrain();
        
        return $this->map;
    }
    
    /**
     * Generate basic landmasses
     */
    private function generateLandmasses() {
        $centerX = $this->width / 2;
        $centerY = $this->height / 2;
        
        // Create multiple land seeds
        $numSeeds = rand(9, 12);
        
        for ($seed = 0; $seed < $numSeeds; $seed++) {
            $landX = rand(10, $this->width - 10);
            $landY = rand(10, $this->height - 10);
            $landSize = rand(20, 50);
            
            // Create circular landmass
            for ($y = 0; $y < $this->height; $y++) {
                for ($x = 0; $x < $this->width; $x++) {
                    $distance = sqrt(pow($x - $landX, 2) + pow($y - $landY, 2));
                    $noise = $this->noise($x * 0.1, $y * 0.1) * 3;
                    
                    if ($distance + $noise < $landSize) {
                        $this->map[$y][$x] = self::PLAINS;
                    }
                }
            }
        }
    }
    
    /**
     * Add terrain variation (forests, mountains, etc.)
     */
    private function addTerrainVariation() {
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                if ($this->map[$y][$x] == self::PLAINS) {
                    $noise1 = $this->noise($x * 0.05, $y * 0.05);
                    $noise2 = $this->noise($x * 0.15, $y * 0.15);
                    $elevation = $noise1 + $noise2 * 0.5;
                    
                    if ($elevation > 0.6) {
                        $this->map[$y][$x] = self::MOUNTAINS;
                    } elseif ($elevation > 0.1) {
                        $this->map[$y][$x] = self::FOREST;
                    } elseif ($elevation < -0.5) {
                        $this->map[$y][$x] = self::SWAMP;
                    } elseif ($elevation < -0.9) {
                        $this->map[$y][$x] = self::WATER;
                    }
                    
                    // Add some desert areas
                    $desertNoise = $this->noise($x * 0.08, $y * 0.08);
                    if ($desertNoise > 0.7 && $elevation > 0) {
                        $this->map[$y][$x] = self::DESERT;
                    }
                }
            }
        }
    }
    
    /**
     * Smooth terrain to make it more natural
     */
    private function smoothTerrain() {
        $newMap = $this->map;
        
        for ($y = 1; $y < $this->height - 1; $y++) {
            for ($x = 1; $x < $this->width - 1; $x++) {
                $neighbors = [
                    $this->map[$y-1][$x-1], $this->map[$y-1][$x], $this->map[$y-1][$x+1],
                    $this->map[$y][$x-1],   $this->map[$y][$x],   $this->map[$y][$x+1],
                    $this->map[$y+1][$x-1], $this->map[$y+1][$x], $this->map[$y+1][$x+1]
                ];
                
                // Count terrain types
                $counts = array_count_values($neighbors);
                arsort($counts);
                
                // If current terrain is minority, consider changing it
                $currentTerrain = $this->map[$y][$x];
                $mostCommon = array_keys($counts)[0];
                
                if ($counts[$currentTerrain] <= 2 && $counts[$mostCommon] >= 5) {
                    $newMap[$y][$x] = $mostCommon;
                }
            }
        }
        
        $this->map = $newMap;
    }
    
    /**
     * Simple noise function for terrain generation
     */
    private function noise($x, $y) {
        $n = sin($x) * sin($y) + sin($x * 2.5) * sin($y * 2.5) * 0.5;
        return ($n + 1) / 2; // Normalize to 0-1
    }
    
    /**
     * Display map as ASCII
     */
    public function displayAscii() {
        echo "\n=== Generated Land Map (ASCII) ===\n";
        echo "Legend: ~ = Water, . = Plains, T = Forest, ^ = Mountains, s = Desert, M = Swamp\n\n";
        
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                echo $this->terrainSymbols[$this->map[$y][$x]];
            }
            echo "\n";
        }
        echo "\n";
    }
    
    /**
     * Generate HTML output with colors
     */
    public function generateHtml($filename = 'map.html') {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Generated Land Map</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .map { display: inline-block; border: 2px solid #333; }
        .row { height: 2px; }
        .cell { 
            display: inline-block; 
            width: 2px; 
            height: 2px; 
            margin: 0;
            padding: 0;
        }
        .legend { margin: 20px; text-align: left; display: inline-block; }
        .legend-item { margin: 5px 0; }
        .legend-color { 
            display: inline-block; 
            width: 20px; 
            height: 20px; 
            margin-right: 10px;
            vertical-align: middle;
            border: 1px solid #000;
        }
    </style>
</head>
<body>
    <h1>Generated Land Map (' . $this->width . 'x' . $this->height . ')</h1>
    
    <div class="legend">
        <h3>Legend:</h3>';
        
        $terrainNames = [
            self::WATER => 'Water',
            self::PLAINS => 'Plains', 
            self::FOREST => 'Forest',
            self::MOUNTAINS => 'Mountains',
            self::DESERT => 'Desert',
            self::SWAMP => 'Swamp'
        ];
        
        foreach ($terrainNames as $type => $name) {
            $html .= '<div class="legend-item">
                <span class="legend-color" style="background-color: ' . $this->terrainColors[$type] . ';"></span>
                ' . $name . '
            </div>';
        }
        
        $html .= '</div>
    
    <div class="map">';
        
        for ($y = 0; $y < $this->height; $y++) {
            $html .= '<div class="row">';
            for ($x = 0; $x < $this->width; $x++) {
                $terrain = $this->map[$y][$x];
                $color = $this->terrainColors[$terrain];
                $html .= '<span class="cell" style="background-color: ' . $color . ';"></span>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>
    
    <p>Refresh the page to generate a new map!</p>
    
</body>
</html>';
        
        file_put_contents($filename, $html);
        echo "HTML map saved to: $filename\n";
        return $html;
    }
    
    /**
     * Get map statistics
     */
    public function getStats() {
        $stats = array_fill_keys([
            self::WATER, self::PLAINS, self::FOREST, 
            self::MOUNTAINS, self::DESERT, self::SWAMP
        ], 0);
        
        $total = $this->width * $this->height;
        
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $stats[$this->map[$y][$x]]++;
            }
        }
        
        echo "\n=== Map Statistics ===\n";
        $terrainNames = [
            self::WATER => 'Water',
            self::PLAINS => 'Plains', 
            self::FOREST => 'Forest',
            self::MOUNTAINS => 'Mountains',
            self::DESERT => 'Desert',
            self::SWAMP => 'Swamp'
        ];
        
        foreach ($stats as $terrain => $count) {
            $percentage = round(($count / $total) * 100, 1);
            echo $terrainNames[$terrain] . ": $count tiles ($percentage%)\n";
        }
        echo "\n";
    }
    
    /**
     * Export map as 2D array
     */
    public function getMapArray() {
        return $this->map;
    }
    
    /**
     * Save map to JSON file
     */
    public function saveToJson($filename = 'map.json') {
        $mapData = [
            'width' => $this->width,
            'height' => $this->height,
            'map' => $this->map,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents($filename, json_encode($mapData, JSON_PRETTY_PRINT));
        echo "Map data saved to: $filename\n";
    }
}

// Usage example
echo "=== 2D Random Square Land Map Generator ===\n\n";

// Create a new map generator (50x50 is default)
$generator = new TerrainMapGenerator(200, 200);

// Generate the map with a random seed
$map = $generator->generateMap();

// Show statistics
$generator->getStats();

// Generate HTML version
$generator->generateHtml('terrain_map.html');

// Save to JSON
$generator->saveToJson('terrain_map.json');

echo "Map generation complete!\n";
echo "- ASCII version displayed above\n";
echo "- HTML version saved as 'terrain_map.html'\n";
echo "- JSON data saved as 'terrain_map.json'\n";

?>