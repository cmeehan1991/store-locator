var $ = require('jquery');
import apiFetch from '@wordpress/api-fetch';
import List from 'list.js';

(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
	key: $('.store-locator').data('key'),
	v: "weekly",
	// Use the 'v' parameter to indicate the version to use (weekly, beta, alpha, etc.).
	// Add other bootstrap parameters as needed, using camel case.
  });

let map;
let geocoder;
let stores;
let storesList;
let userLat
let userLng;
let zoom;

$(document).ready(function(){
	getCurrentLocation();
	userLat = $('.store-locator').data('lat');
	userLng = $('.store-locator').data('lng');
	zoom = $('.store-locator').data('zoom');

	initMap();
});

function addMarkers(map, data){
	Object.values(data).forEach( val => {

		const latLng = {lat: val.latitude, lng: val.longitude};

		const infoString =
		'<div id="content">' +

		'<h1>' + val.name + ' </h1>' +

	    '<p>' + val.fullAddressHTML + '</p>' +

		'<a href="' + val.phoneNumberLink + '">' + val.phoneNumber + '</a><br/>'+

		'<a href="' + val.emailAddressLink + '">' + val.emailAddress + '</a><br/>'+

		'<a href="' + val.website + '">' + val.website + '</a><br/>' +

		'</div>'

		const infoWindow = new google.maps.InfoWindow({
			content: infoString,
			ariaLabel: val.name
		});

		const marker = new google.maps.Marker({
			position: latLng,
			map,
			title: val.name
		});

		marker.addListener("click", () => {
			infoWindow.open({
				anchor: marker,
				map,
			});
		});
	})
}

function getStores(map){
	apiFetch({
		path: 'cbm-store-locator/v1/stores',
		method: 'GET'
	}).then((data) => {
		stores = data;
		addMarkers(map, stores);
		createList(stores);
	}).catch((error) => {
		console.error(error);
	});
}

window.doSearchLocation = function(){

	let location = $('.location-input').val();
	if(location) {
		geocoder.geocode({'address': location}, function(results, status){
			if(status == google.maps.GeocoderStatus.OK){

				userLat = results[0].geometry.location.lat();

				userLng = results[0].geometry.location.lng();

				recenterMap(userLat, userLng);
				filterResults(userLat, userLng);
			}
		} );
	}
	return false;
}

window.getCurrentLocation = function(){
	navigator.permissions.query({name: 'geolocation'}).then(permission => {

		if(permission.state === 'denied'){
			window.alert('Please enable your location');
		}

	});

	if(navigator.geolocation){

		console.log('get location');

		navigator.geolocation.getCurrentPosition(function(position){
			reverseGeocode(position.coords.latitude, position.coords.longitude);
			recenterMap(position.coords.latitude, position.coords.longitude);
		})
	}
}

function filterResults(lat, lng){

	let userLocation = new google.maps.LatLng(lat, lng);

	stores.forEach(function(store){
		let storeLocation = new google.maps.LatLng(store.latitude, store.longitude);

		let distance = google.maps.geometry.spherical.computeDistanceBetween(userLocation, storeLocation);

		store.distance = distance;

	});

	stores.sort((a,b) => a.distance - b.distance);
	createList(stores);

}

function reverseGeocode(lat, lng){


	const latLng = {
		lat: parseFloat(lat),
		lng: parseFloat(lng)
	};

	geocoder.geocode({location: latLng })
	.then(response => {
		if(response.results[0]){
			$('.location-input').val(response.results[0].formatted_address);
			filterResults(lat, lng);
		}
	})
	.finally(() => {
		console.log('here');
	})
}

function createList(data) {

	var itemHtml = '<li>';

	itemHtml += '<h3 class="name"></h3>';

	itemHtml += '<p class="fullAddressHTML"></p>';

	itemHtml += '<a class="phoneNumberLink"><span class="phoneNumber"></span></a><br/>';

	itemHtml += '<a class="emailAddressLink"><span class="emailAddress"></span></a><br/>';

	itemHtml += '<a class="website"><span class="website"></span></a><br/>';

	itemHtml += '</li>';

	var options  = {
		valueNames: ["name", "fullAddressHTML", {attr: 'href', name:'phoneNumberLink'}, "phoneNumber", {attr: 'href', name:'emailAddressLink'}, 'emailAddress', {attr: 'href', name: 'website'}, 'website'],
		item: itemHtml,
		pagination: true,
		page: 25
	};

	storesList = new List('storeLocatorListSection', options);

	storesList.clear().add(data);

}

function recenterMap(userLat, userLng){
	var location = new google.maps.LatLng(userLat, userLng);
	map.setCenter(location);
}

async function initMap(){
	const {Map} = await google.maps.importLibrary("maps");

	// Initialize the geocoder
	geocoder = google.maps.Geocoder;

	const {Geocoder} = await google.maps.importLibrary("geocoding");
	const {spherical} = await google.maps.importLibrary("geometry");


	geocoder = new Geocoder();

	geocoder.geocode({'address': '320 Burlington Ave, Gibsonvile, NC 27249'}, function(results, status){
		if(status == google.maps.GeocoderStatus.OK){

			userLat = results[0].geometry.location.lat();

			userLng = results[0].geometry.location.lng();

			recenterMap(userLat, userLng);
		}
	});

	map = new Map(document.getElementById("storeLocatorMap"), {
		center: { lat: userLat, lng: userLng },
		zoom: zoom,
	});

	google.maps.event.addListener(map, "dragend", function(e){
		userLat = map.getCenter().lat();
		userLng = map.getCenter().lng();

		filterResults(userLat, userLng);
	});

	getStores(map);
}

