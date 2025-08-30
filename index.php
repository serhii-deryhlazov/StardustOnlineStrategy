<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

function getPlayers() {
    $playersByPlanet = [];
    $basePath = __DIR__ . '/systems/solar';
    if (!is_dir($basePath)) {
        return $playersByPlanet;
    }

    $planets = scandir($basePath);
    foreach ($planets as $planet) {
        if ($planet === '.' || $planet === '..') continue;
        $planetPath = $basePath . '/' . $planet;
        if (is_dir($planetPath)) {
            $playersByPlanet[$planet] = [];
            $playerFiles = scandir($planetPath);
            foreach ($playerFiles as $playerFile) {
                if (pathinfo($playerFile, PATHINFO_EXTENSION) === 'json') {
                    $content = file_get_contents($planetPath . '/' . $playerFile);
                    $playerData = json_decode($content, true);
                    if (isset($playerData['username'])) {
                        $playersByPlanet[$planet][] = $playerData['username'];
                    }
                }
            }
        }
    }
    return $playersByPlanet;
}

$playersData = getPlayers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Solar System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="solar-system">
        <div id="sun"></div>
        <div class="orbit" data-planet="Mercury" data-duration="16s">
            <div class="planet" id="Mercury" data-planet="Mercury"></div>
        </div>
        <div class="orbit" data-planet="Venus" data-duration="24s">
            <div class="planet" id="Venus" data-planet="Venus"></div>
        </div>
        <div class="orbit" data-planet="Earth" data-duration="40s">
            <div class="planet" id="Earth" data-planet="Earth"></div>
        </div>
        <div class="orbit" data-planet="Mars" data-duration="60s">
            <div class="planet" id="Mars" data-planet="Mars"></div>
        </div>
        <div class="orbit" data-planet="Jupiter" data-duration="100s">
            <div class="planet" id="Jupiter" data-planet="Jupiter"></div>
        </div>
        <div class="orbit" data-planet="Saturn" data-duration="160s">
            <div class="planet" id="Saturn" data-planet="Saturn"></div>
        </div>
        <div class="orbit" data-planet="Uranus" data-duration="240s">
            <div class="planet" id="Uranus" data-planet="Uranus"></div>
        </div>
        <div class="orbit" data-planet="Neptune" data-duration="320s">
            <div class="planet" id="Neptune" data-planet="Neptune"></div>
        </div>
    </div>

    <div class="planet-view">
        <h2 id="planet-view-title"></h2>
        <div class="quadrant-selector-container">
            <p>Select quadrant:</p>
            <div class="quadrant-selector">
                <div class="quadrant" data-quadrant="1"></div>
                <div class="quadrant" data-quadrant="2"></div>
                <div class="quadrant" data-quadrant="3"></div>
                <div class="quadrant" data-quadrant="4"></div>
            </div>
        </div>
    </div>

    <div id="tooltip"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const playersData = <?php echo json_encode($playersData); ?>;

        $(document).ready(function() {
            function setupSolarSystem() {
                const availableSize = Math.min($(window).width(), $(window).height());
                const solarSystemSize = availableSize * 0.95; // Use 95% of available space
                const maxRadius = solarSystemSize / 2;
                const sunSize = solarSystemSize * 0.05;

                $('.solar-system').css({
                    width: solarSystemSize + 'px',
                    height: solarSystemSize + 'px'
                });

                $('#sun').css({
                    width: sunSize + 'px',
                    height: sunSize + 'px'
                });

                const orbitRatios = {
                    Mercury: 0.15,
                    Venus: 0.25,
                    Earth: 0.35,
                    Mars: 0.50,
                    Jupiter: 0.65,
                    Saturn: 0.80,
                    Uranus: 0.90,
                    Neptune: 1.0
                };

                $('.orbit').each(function() {
                    const planetName = $(this).data('planet');
                    const ratio = orbitRatios[planetName];
                    const radius = maxRadius * ratio;
                    const duration = $(this).data('duration');
                    
                    $(this).css({
                        '--orbit-radius': radius + 'px',
                        '--orbit-duration': duration
                    });
                });
            }

            setupSolarSystem();
            $(window).on('resize', setupSolarSystem);


            $('.planet').on('click', function() {
                const planetName = $(this).data('planet');
                $('#planet-view-title').text(planetName);
                $('.planet-view').show();
                $('.quadrant').removeClass('active');
            });

            $('.quadrant').on('click', function() {
                $('.quadrant').removeClass('active');
                $(this).addClass('active');
                const quadrant = $(this).data('quadrant');
                console.log(`Selected quadrant: ${quadrant}`);
                // Here you can add logic to show the selected part of the planet
            });

            $('.planet').on('mouseenter', function(e) {
                const planetName = $(this).data('planet');
                const players = playersData[planetName] || [];
                let tooltipContent = `<b>${planetName}</b>`;

                if (players.length > 0) {
                    tooltipContent += '<ul>';
                    players.forEach(player => {
                        tooltipContent += `<li>${player}</li>`;
                    });
                    tooltipContent += '</ul>';
                } else {
                    tooltipContent += '<p>empty</p>';
                }

                $('#tooltip').html(tooltipContent).css({
                    display: 'block',
                });
            }).on('mouseleave', function() {
                $('#tooltip').hide();
            });

            $(document).on('mousemove', function(e) {
                $('#tooltip').css({
                    left: e.pageX + 15,
                    top: e.pageY + 15
                });
            });
        });
    </script>
</body>
</html>
