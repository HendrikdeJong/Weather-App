# Documentatie Frontend

## Overzicht

Deze frontend is gebouwd met moderne webtechnieken (TypeScript, Vite, Vue 3) en volgt best practices op het gebied van structuur, onderhoudbaarheid en performance.

## Structuur

- `src/components/`: Herbruikbare UI-componenten.
- `src/pages/`: Pagina's voor de router.
- `src/services/`: API-aanroepen en externe logica.
- `src/stores/`: State management (bijv. Pinia).
- `src/assets/`: Afbeeldingen en statische bestanden.
- `src/styles/`: Globale en component-specifieke stijlen.

## Belangrijke Features

- TypeScript voor typeveiligheid.
- Code-splitting en lazy loading voor performance.
- PWA-ondersteuning (offline, manifest, service worker).
- Toegankelijkheid: toetsenbordnavigatie, contrast, screenreader.
- Data-visualisatie (bijv. temperatuur-grafieken).

## Ontwikkelen & Bouwen

- Ontwikkel: `npm run dev`
- Build: `npm run build`
- Testen: `npm run test`

## Best practices

- Duidelijke naamgeving en structuur.
- Scheiding van concerns.
- Uitbreidbaar en onderhoudbaar opgezet.

## Zie ook

- [README-KWALITEIT.md](./README-KWALITEIT.md) voor kwaliteitsaspecten.
