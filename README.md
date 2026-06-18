# TAFARAY-SENI · Gestion Réception des Novices

Application PHP de gestion des étudiants (Anciens / Novices) et de leurs
droits de réception, avec génération de **QR Code personnel en PDF** et
d'un **PDF unique regroupant tous les QR Codes** (7 par page, 1 par ligne, A4).

## Pile technique

- PHP 8+ (PDO SQLite intégré, aucune configuration de base de données)
- FPDF (inclus dans `lib/fpdf.php`) pour la génération PDF
- API publique `api.qrserver.com` pour le rendu des QR codes (nécessite Internet sur le serveur)
- Design sombre / doré professionnel (CSS pur, sans framework)

## Installation

1. Décompressez l'archive dans le `htdocs` (XAMPP / WAMP / MAMP) ou un dossier servi par PHP.
2. Assurez-vous que les extensions PHP `pdo_sqlite`, `gd` et `openssl` sont actives (par défaut sur XAMPP).
3. Lancez :

```bash
cd tafaray-seni
php -S localhost:8000
```

Puis ouvrez http://localhost:8000

La base SQLite est créée automatiquement dans `data/tafaray.db` au premier lancement.

## Structure

```
tafaray-seni/
├── index.php           # Tableau de bord
├── etudiants.php       # CRUD étudiants
├── droits.php          # CRUD paiements / droits
├── qr.php?id=X         # PDF QR code personnel (A4)
├── qr_all.php          # PDF unique - tous les QR (7 / page)
├── includes/
│   ├── db.php          # Connexion SQLite + helpers
│   ├── header.php
│   └── footer.php
├── assets/style.css    # Design system sombre & doré
├── lib/                # FPDF (fpdf.php + fonts)
└── data/tafaray.db     # Base de données (auto-créée)
```

## Tables (selon le cahier des charges)

- **Etudiant**(NomEt, PrenomEt, GenerationEt {Ancien|Novice}, LogementsEt {Interne|Externe})
- **Droit**(MontantPayee, RestePayer, DatePaiement) — relié à un étudiant

## QR Code

Chaque QR encode :

```
SENI-2026-NomEt.PrenomEt
Nom / Prénom / Génération / Logement
Montant payé / Reste à payer / Date paiement
```

- `qr.php?id=X` → PDF A4 individuel (carte d'identification).
- `qr_all.php` → PDF A4 multi-pages, **7 QR par page, un QR par ligne**.

## Personnalisation rapide

- Couleurs : `assets/style.css` — variables `--accent`, `--bg`, etc.
- En-tête du PDF : voir `qr.php` et `qr_all.php` (RGB doré 212,168,76).

Bonne réception à tous les novices 🎓
