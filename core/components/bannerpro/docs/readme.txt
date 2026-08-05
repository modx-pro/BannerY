BannerPro 4.5.0 — manage banner ads in MODX 3.

Requires: pdoTools (frontend snippet), VueTools (admin UI).
Optional: MiniShop3 (product_id, product_* placeholders, &productId=).

Build admin bundle (from repo root):
  npm install && npm run build

Features: positions, context_key per position, scheduling, clicks, impressions, CTR dashboard,
CSV export, snippet HTML cache, image/HTML banner types, positionName/positionKey, Vue 3 admin,
OnBannerProClick/OnBannerProImpression, click webhook (4.5), duplicate ad/position, UTM on click,
optional BannerProAnalytics (GA4, Matomo, YM), read-only REST API (4.3).

Targets MODX 3.x and PHP 8.2+. Standalone banner extra; DB tables bannerpro_*.

Documentation (repo): docs/README.md
