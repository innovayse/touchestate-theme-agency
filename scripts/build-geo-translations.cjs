/**
 * Build compact translation maps from @innovayse/geo-atlas (offline data).
 *
 * The TouchEstate API returns city/country as single English strings
 * ("Yerevan", "Armenia"). geo-atlas bundles en/ru/hy names. We extract them into
 * tiny JSON files keyed by the lowercased English name so PHP can localize
 * server-side without shipping the 171 MB dataset or hitting an API.
 *
 *   resources/data/city-translations.json     — cities of the given country/countries (default AM)
 *   resources/data/country-translations.json  — all 250 countries
 *
 * Usage:  node scripts/build-geo-translations.cjs [ISO2 ...]   (default: AM)  (npm run build:geo)
 */
const { writeFileSync, mkdirSync } = require('fs');
const { dirname, join } = require('path');
const { getStatesForCountry } = require('@innovayse/geo-atlas/cities');
const Lite = require('@innovayse/geo-atlas/lite');
const Countries = Lite.default || Lite;

const DATA = join(__dirname, '..', 'resources', 'data');
const COUNTRIES = (process.argv.slice(2).length ? process.argv.slice(2) : ['AM']).map(c => c.toUpperCase());

const norm = s => (s || '').toString().trim();
const key = s => norm(s).toLowerCase();

function row(map, entry) {
  const en = norm(entry.name);
  if (!en) return;
  const hy = norm(entry.translations && entry.translations.hy) || norm(entry.native);
  const ru = norm(entry.translations && entry.translations.ru);
  if (!hy && !ru) return;                 // nothing to translate → skip
  const k = key(en);
  const prev = map[k] || { en };
  map[k] = { en, ru: prev.ru || ru || en, hy: prev.hy || hy || en };
}

(async () => {
  mkdirSync(DATA, { recursive: true });

  // Cities — of the requested country/countries.
  const cityMap = {};
  let cities = 0, states = 0;
  for (const iso2 of COUNTRIES) {
    for (const st of await getStatesForCountry(iso2)) {
      states++;
      row(cityMap, st);                                       // region (e.g. Yerevan)
      for (const c of (st.cities || [])) { cities++; row(cityMap, c); } // towns (e.g. Jrvezh)
    }
  }
  writeFileSync(join(DATA, 'city-translations.json'), JSON.stringify(cityMap, null, 0));

  // Countries — all of them (small).
  const countryMap = {};
  for (const c of Countries.getCountries()) row(countryMap, c);
  writeFileSync(join(DATA, 'country-translations.json'), JSON.stringify(countryMap, null, 0));

  console.log(`cities: countries=${COUNTRIES.join(',')} states=${states} scanned=${cities} mapped=${Object.keys(cityMap).length}`);
  console.log(`countries: mapped=${Object.keys(countryMap).length}`);
  for (const p of ['Yerevan', 'Jrvezh']) console.log(`  city ${p} ->`, JSON.stringify(cityMap[key(p)] || null));
  for (const p of ['Armenia', 'Russia']) console.log(`  country ${p} ->`, JSON.stringify(countryMap[key(p)] || null));
})();
