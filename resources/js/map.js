/*
Author       : Dreamguys
Template Name: DreamsEstate - Bootstrap Template
Version      : 1.0
Modified     : Yandex Maps Integration
*/

var map, current = 0;
var slider;

var locations = [
  {
    "id": 1,
    "lat": 53.470692,
    "lng": -2.220328,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "1500",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "total_review": "17",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "build/img/product/product-1.jpg",
    "profile_image": "build/img/profiles/avatar-01.jpg"
  },
  {
    "id": 2,
    "lat": 53.469189,
    "lng": -2.199262,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "1000",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "rent_address": "470 Park Ave S, New York, NY 10016",
    "total_review": "17",
    "image": "build/img/product/product-2.jpg",
    "profile_image": "build/img/profiles/avatar-02.jpg"
  },
  {
    "id": 3,
    "lat": 53.468665,
    "lng": -2.189269,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "5000",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "total_review": "17",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "build/img/product/product-3.jpg",
    "profile_image": "build/img/profiles/avatar-03.jpg"
  },
  {
    "id": 4,
    "lat": 53.463894,
    "lng": -2.177880,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "350",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "total_review": "17",
    "rent_address": "470 Park Ave S, New York, NY 10016",
    "image": "build/img/product/product-4.jpg",
    "profile_image": "build/img/profiles/avatar-04.jpg"
  },
  {
    "id": 5,
    "lat": 53.466359,
    "lng": -2.213314,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "1100",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "total_review": "17",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "build/img/product/product-5.jpg",
    "profile_image": "build/img/profiles/avatar-05.jpg"
  },
  {
    "id": 6,
    "lat": 53.469189,
    "lng": -2.210661,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "300",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "rent_address": "470 Park Ave S, New York, NY 10016",
    "total_review": "17",
    "image": "build/img/product/product-2.jpg",
    "profile_image": "build/img/profiles/avatar-02.jpg"
  },
  {
    "id": 7,
    "lat": 53.468665,
    "lng": -2.188532,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "3000",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "total_review": "17",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "build/img/product/product-3.jpg",
    "profile_image": "build/img/profiles/avatar-03.jpg"
  },
  {
    "id": 8,
    "lat": 53.463894,
    "lng": -2.1950372,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "1000",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "total_review": "17",
    "rent_address": "470 Park Ave S, New York, NY 10016",
    "image": "build/img/product/product-4.jpg",
    "profile_image": "build/img/profiles/avatar-04.jpg"
  },
  {
    "id": 9,
    "lat": 53.466359,
    "lng": -2.203314,
    "rent_prize": "$1,400 ",
    "rent_bed": "4",
    "rent_baths": "4",
    "rent_sqft": "2000",
    "rent_listedon": "17 Jan 2023",
    "rent_Category": "Condos",
    "rent_name": "Place perfect for nature",
    "total_review": "17",
    "rent_address": "122-140 N Morgan St, Chicago, IL 60607, USA",
    "image": "build/img/product/product-5.jpg",
    "profile_image": "build/img/profiles/avatar-05.jpg"
  }
];

function getBalloonContent(marker) {
  return `
    <div class="buy-list">
      <div class="product-custom">
        <div class="profile-widget rent-list-view">
          <div class="doc-img">
            <a href="javascript:void(0)" class="property-img">
              <img class="img-fluid" alt="img" src="${marker.image}">
            </a>
            <a href="javascript:void(0)">
              <div class="favourite">
                <span><i class="fa-regular fa-heart"></i></span>
              </div>
            </a>
            <div class="user-avatar">
              <img src="${marker.profile_image}" alt="Image">
            </div>
          </div>
          <div class="pro-content">
            <div class="list-head">
              <div class="rating">
                <i class="fa-solid fa-star checked"></i>
                <i class="fa-solid fa-star checked"></i>
                <i class="fa-solid fa-star checked"></i>
                <i class="fa-solid fa-star checked"></i>
                <i class="fa-solid fa-star checked"></i>
                <span>5.0 (${marker.total_review} Reviews)</span>
                <div class="product-name-price">
                  <h3 class="title d-flex align-items-center justify-content-between">
                    <a href="javascript:void(0)" tabindex="-1">${marker.rent_name}</a>
                  </h3>
                  <div class="product-amount">
                    <h5><span>${marker.rent_prize}</span></h5>
                  </div>
                </div>
                <p><i class="feather-map-pin"></i> ${marker.rent_address}</p>
              </div>
            </div>
            <ul class="d-flex details">
              <li>
                <img src="build/img/icons/bed-icon.svg" alt="bed-icon">
                <span>${marker.rent_bed} Beds </span>
              </li>
              <li>
                <img src="build/img/icons/bath-icon.svg" alt="bath-icon">
                <span>${marker.rent_baths} Baths </span>
              </li>
              <li>
                <img src="build/img/icons/building-icon.svg" alt="building-icon">
                <span>${marker.rent_sqft} Sqft </span>
              </li>
            </ul>
            <ul class="property-category d-flex justify-content-between">
              <li>
                <span class="list">Listed on : </span>
                <span class="date">${marker.rent_listedon}</span>
              </li>
              <li>
                <span class="category list">Category : </span>
                <span class="category-value date">${marker.rent_Category}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  `;
}

function initialize() {
  ymaps.ready(function () {
    // Create map centered on first location
    map = new ymaps.Map('map', {
      center: [locations[0].lat, locations[0].lng],
      zoom: 14,
      controls: ['zoomControl', 'typeSelector', 'fullscreenControl']
    });

    var placemarks = [];

    // Add placemarks (markers) for each location
    locations.forEach(function(location, index) {
      var placemark = new ymaps.Placemark(
        [location.lat, location.lng],
        {
          balloonContent: getBalloonContent(location),
          hintContent: location.rent_name
        },
        {
          preset: 'islands#redIcon',
          hideIconOnBalloonOpen: false
        }
      );

      // Add placemark to map
      map.geoObjects.add(placemark);
      placemarks.push(placemark);
    });

    // Auto-cycle through balloons
    var currentBalloonIndex = 0;
    map.slide = true;

    function showNextBalloon() {
      if (!map.slide || placemarks.length === 0) return;

      // Close all balloons first
      placemarks.forEach(function(pm) {
        pm.balloon.close();
      });

      // Select random or next balloon
      var next;
      if (placemarks.length === 1) {
        next = 0;
      } else {
        do {
          next = Math.floor(Math.random() * placemarks.length);
        } while (next === currentBalloonIndex && placemarks.length > 1);
      }

      currentBalloonIndex = next;

      // Open balloon
      placemarks[currentBalloonIndex].balloon.open();
    }

    // Start auto-cycling every 3 seconds
    slider = setInterval(showNextBalloon, 3000);

    // Stop auto-cycling when zoom > 16
    map.events.add('boundschange', function() {
      if (map.getZoom() > 16) {
        map.slide = false;
        clearInterval(slider);
      }
    });

    // Fit map bounds to show all markers
    if (placemarks.length > 0) {
      var bounds = map.geoObjects.getBounds();
      map.setBounds(bounds, {
        checkZoomRange: true,
        zoomMargin: 50
      });
    }
  });
}

// Initialize when DOM is ready
if (typeof ymaps !== 'undefined') {
  initialize();
} else {
  console.error('Yandex Maps API not loaded');
}
