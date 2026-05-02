// Campus Relief Hub - Main JS (Map, Nutrition, Nearby Food)

let map = null;


// Initialize Leaflet map at ZPPSU campus
function initMap() {
    if (map || !document.getElementById('campusMap')) return;
    const ZPPSU = [6.9214, 122.0790];
    map = L.map('campusMap').setView(ZPPSU, 16);
    L.tileLayer('https://a.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenTopoMap | ZPPSU Relief Hub',
        maxZoom: 18
    }).addTo(map);
    L.marker(ZPPSU).addTo(map)
        .bindPopup('<strong>ZPPSU Campus Relief Hub</strong><br>Food pickup location')
        .openPopup();
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('campusMap')) {
        initMap();
    }
});

// Geolocation
function getUserLocation(callback) {
    navigator.geolocation.getCurrentPosition(
        pos => callback({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
        () => {
            fetch('https://ipapi.co/json/')
                .then(r => r.json())
                .then(d => callback({ lat: d.latitude, lng: d.longitude }))
                .catch(() => callback({ lat: 6.9214, lng: 122.0790 }));
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 600000 }
    );
}

// Reverse geocode
function reverseGeocode(lat, lng) {
    return fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(r => r.json())
        .then(d => d.display_name || 'ZPPSU Campus Area')
        .catch(() => 'Your Area');
}

// Nearby food via Overpass
async function getNearbyFood(lat, lng) {
    const resultDiv = document.getElementById('nearbyFoodResults');
    if (!resultDiv) return;
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Searching nearby food places...</p></div>';

    try {
        const url = `https://overpass-api.de/api/interpreter?data=[out:json][timeout:25];(node(around:5000,${lat},${lng})["amenity"~"restaurant|fast_food|cafe"];);out%20geom;`;
        const res = await fetch(url);
        const data = await res.json();
        const places = (data.elements || []).slice(0, 6).map(p => ({
            name: p.tags?.name || 'Food Place',
            type: p.tags?.amenity || 'restaurant',
            lat: p.lat, lng: p.lon
        }));

        if (places.length > 0) {
            const locName = await reverseGeocode(lat, lng);
            resultDiv.innerHTML = `
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0 fw-bold"><i class="fas fa-utensils text-primary me-2"></i>Nearby Food in ${locName}</h5>
                    <small class="text-muted">Within 5km of (${lat.toFixed(4)}, ${lng.toFixed(4)})</small></div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            ${places.map(p => `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div><strong>${p.name}</strong><br><small class="text-muted"><i class="fas fa-map-pin"></i> ${p.type}</small></div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="card-footer text-center"><small class="text-muted">Data from OpenStreetMap (Free)</small></div>
                </div>`;

            places.forEach(p => {
                if (p.lat && p.lng) {
                    L.marker([p.lat, p.lng]).addTo(map).bindPopup(`<strong>${p.name}</strong><br>${p.type}`);
                }
            });
        } else {
            resultDiv.innerHTML = '';
            resultDiv.style.display = 'none';
            if (window.crhToast) crhToast('warning', 'No nearby food places found within 5km.', 'No Results');
        }
    } catch (e) {
        resultDiv.innerHTML = '';
        resultDiv.style.display = 'none';
        if (window.crhToast) crhToast('danger', 'Error loading nearby places. Check your connection.', 'Connection Error');
    }
}

function findNearbyFood() {
    if (!map) initMap();
    getUserLocation(loc => {
        L.marker([loc.lat, loc.lng]).addTo(map).bindPopup('Your location').openPopup();
        map.setView([loc.lat, loc.lng], 14);
        getNearbyFood(loc.lat, loc.lng);
    });
}

// USDA Nutrition Lookup
async function searchNutrition() {
    const query = document.getElementById('nutritionQuery')?.value.trim();
    const resultDiv = document.getElementById('nutritionResult');
    if (!query || !resultDiv) return;

    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';

    try {
        const res = await fetch(`https://api.nal.usda.gov/fdc/v1/foods/search?query=${encodeURIComponent(query)}&pageSize=1&api_key=DEMO_KEY`);
        const data = await res.json();

        if (data.foods && data.foods.length > 0) {
            const food = data.foods[0];
            const n = food.foodNutrients || [];
            const cal = n.find(x => x.nutrientName === 'Energy')?.value || 'N/A';
            const pro = n.find(x => x.nutrientName === 'Protein')?.value || 'N/A';
            const carb = n.find(x => x.nutrientName === 'Carbohydrate, by difference')?.value || 'N/A';
            const fat = n.find(x => x.nutrientName === 'Total lipid (fat)')?.value || 'N/A';

            resultDiv.innerHTML = `
                <h6 class="fw-bold text-primary">${food.description}</h6>
                <div class="row g-2 mt-2">
                    <div class="col-6"><div class="bg-white p-2 rounded text-center"><strong>${cal}</strong><br><small class="text-muted">kcal</small></div></div>
                    <div class="col-6"><div class="bg-white p-2 rounded text-center"><strong>${pro}g</strong><br><small class="text-muted">Protein</small></div></div>
                    <div class="col-6"><div class="bg-white p-2 rounded text-center"><strong>${carb}g</strong><br><small class="text-muted">Carbs</small></div></div>
                    <div class="col-6"><div class="bg-white p-2 rounded text-center"><strong>${fat}g</strong><br><small class="text-muted">Fat</small></div></div>
                </div>
                <p class="mt-2 mb-0"><small class="text-muted">Source: USDA FoodData Central</small></p>`;
        } else {
            resultDiv.style.display = 'none';
            if (window.crhToast) crhToast('warning', 'No results found. Try "rice" or "chicken".', 'No Match');
        }
    } catch (e) {
        resultDiv.style.display = 'none';
        if (window.crhToast) crhToast('danger', 'Error loading nutrition data. Please try again.', 'API Error');
    }
}

// Enter key for nutrition search
document.addEventListener('DOMContentLoaded', function () {
    const nq = document.getElementById('nutritionQuery');
    if (nq) {
        nq.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); searchNutrition(); }
        });
    }
});
