# META B2B Experience Hub

Shopware-6-Plugin zur Zuweisung von **CMS-Erlebniswelten** (Typ `page`) an die **B2B Sellers Platform**. Inhalte erscheinen entweder als Tab im Hub **„Inhalte“** oder als **eigener Menüpunkt** in der B2B-Navigation.

| | |
|---|---|
| **Plugin** | `MetaB2bExperienceHub` |
| **Version** | 0.3.1 |
| **Shopware** | 6.7.x |
| **Abhängigkeit** | B2B Sellers Suite Core ^4.0 |

---

## Funktionen

- Admin-Modul **Katalog → B2B Inhalte** zur Verwaltung von Zuweisungen
- Verknüpfung mit CMS-Seiten (`type = page`)
- Sichtbarkeit über **RuleBuilder**, Sales Channel, Zielgruppe (Kunde / Vertrieb)
- **Content-Hub:** mehrere Inhalte als Tabs unter „Inhalte“
- **Einzelmenü:** dedizierter B2B-Platform-Menüpunkt pro Zuweisung
- Store-API für B2B-Frontend mit Login- und Berechtigungsprüfung
- Automatischer Sync der Platform-Menüeinträge bei Speichern
- Activity-Tracking (`experience_view`) über B2B Sellers Activity Service
- Cache-Invalidierung bei Änderungen

---

## Installation

```bash
# Plugin liegt unter app/custom/plugins/MetaB2bExperienceHub
bin/console plugin:refresh
bin/console plugin:install --activate MetaB2bExperienceHub
bin/console plugin:update MetaB2bExperienceHub --no-interaction

# Assets bauen
./bin/build-administration.sh
bin/console b2b:platform:build
bin/console cache:clear
```

Nach Updates mit Admin- oder B2B-Frontend-Änderungen Administration und B2B Platform neu bauen (siehe oben).

---

## Admin: Zuweisungen anlegen

**Navigation:** Katalog → **B2B Inhalte**

### Pflichtfelder

| Feld | Beschreibung |
|---|---|
| Titel | Anzeigename (mehrsprachig) |
| CMS-Seite | Nur Layouts mit Typ **`page`** (keine Kategorie-Layouts) |
| Aktiv | Schaltet die Zuweisung ein/aus |

### Sichtbarkeit

| Feld | Wirkung |
|---|---|
| Für Kunden sichtbar | Sichtbar für B2B-Kunden / Employees |
| Für Vertrieb sichtbar | Sichtbar für eingeloggte Vertriebsmitarbeiter |
| Regel (RuleBuilder) | Optional; leer = für alle im Sales Channel |
| Sales Channel | Optional; leer = alle Sales Channels |

### B2B Platform Menü

| Feld | Wirkung |
|---|---|
| **Als eigener Menüpunkt anzeigen** | Legt einen separaten Navigationspunkt an |
| **Im Content-Hub anzeigen** | Erscheint als Tab unter „Inhalte“ |
| Menü-Parent | Optional; nur Einträge im **Customer-Menübaum** |
| Menü-Icon | Icon-Name (Standard: `regular-content`) |

Beide Optionen können kombiniert werden (Hub + Einzelmenü).

---

## B2B Platform (Storefront)

| URL | Inhalt |
|---|---|
| `/b2b_platform/experiences` | Content-Hub mit Tabs |
| `/b2b_platform/experiences/{id}` | Einzelinhalt (Menü-Deep-Link) |

Menüpunkt **„Inhalte“** im Hub bleibt unter `/b2b_platform/experiences`.

Nach Änderungen an Menü-Sync: B2B Platform hart neu laden (Cmd+Shift+R) oder neu anmelden.

---

## Menü-Synchronisation

Menüeinträge werden in `b2bsellers_platform_menu_item` gepflegt:

| Typ | `technical_name` | `internal_link` |
|---|---|---|
| Hub (Kunde) | `meta_b2b_experiences` | `experiences` |
| Hub (Vertrieb) | `meta_b2b_experiences_sales_rep` | `experiences` |
| Einzel (Kunde) | `meta_b2b_experience_{uuid}` | `experiences/{uuid}` |
| Einzel (Vertrieb) | `meta_b2b_experience_{uuid}_sales_rep` | `experiences/{uuid}` |

### Automatischer Sync

- Beim Speichern/Löschen einer Zuweisung (`ExperiencePlatformMenuSubscriber`)
- Nach Plugin-Aktivierung / -Update (`PluginLifecycleSubscriber`)
- Beim B2B-Menü-Rebuild (`PlatformMenuSubscriber`)

### Manueller Sync

```bash
bin/console meta-b2b-experience:sync-platform-menu
```

---

## Store-API

| Route | Methode | Auth |
|---|---|---|
| `/store-api/meta-b2b-experiences` | GET | Login + B2B-Kontext + `viewB2bExperience` |
| `/store-api/meta-b2b-experiences/{id}` | GET | Login + B2B-Kontext + `viewB2bExperience` |

Sichtbarkeit wird serverseitig gefiltert (aktiv, Rule, Sales Channel, Zielgruppe). Unzulässige IDs liefern **404**.

---

## Berechtigungen & Sicherheit

### Shopware Admin (ACL)

Das Plugin erweitert CMS-Rollen über `enrichPrivileges()`:

| Rolle | Rechte |
|---|---|
| `cms.viewer` | Lesen, CMS-Seiten/Rules/Sales Channels |
| `cms.editor` | Bearbeiten inkl. Rule-Anlage |
| `cms.creator` | Anlegen |
| `cms.deleter` | Löschen |

Admin-Routen und Aktionen (Speichern, Anlegen, Löschen) sind an diese Privileges gebunden.

### B2B Employee Permission

| Key | Beschreibung |
|---|---|
| `viewB2bExperience` | Darf B2B-Inhalte in der Platform einsehen |

Bei Plugin-Update wird die Permission **bestehenden Employee-Rollen** automatisch zugewiesen. Neue Rollen müssen sie ggf. manuell erhalten (B2B-Rollenverwaltung).

Vertriebsmitarbeiter und Kunden-Admins umgehen die Employee-Permission-Prüfung (B2B-Standard).

### Menü-Parent-Validierung

- Admin-Auswahl: nur Platform-Menüeinträge im Customer-Baum
- Sync: ungültige Parent-IDs werden auf den Default-Root zurückgesetzt

---

## Konsole & Wartung

```bash
# Plugin aktualisieren
bin/console plugin:update MetaB2bExperienceHub --no-interaction

# Menü neu synchronisieren
bin/console meta-b2b-experience:sync-platform-menu

# Builds
./bin/build-administration.sh
bin/console b2b:platform:build
bin/console cache:clear
```

---

## Architektur (Kurzüberblick)

```
meta_b2b_experience (Entity)
├── ExperienceResolver          → Sichtbarkeitsfilter Store-API
├── ExperiencePlatformMenuSyncService → Menü-DB-Sync
├── PlatformMenuInstaller       → Hub-Menüpunkte „Inhalte“
├── Admin-Modul meta-b2b-experience
└── B2B-Modul meta_b2b_experiences (routePrefixPath: experiences)
```

**Migrations:** `1781000000` (Basis) → `1781000100` (Phase 2) → `1781000200` (Menü-Felder) → `1781000300` (Employee Permission)

---

## Bekannte Hinweise

- Für sichtbaren CMS-Inhalt **Shopseiten-Layouts** (`type = page`) wählen, nicht Kategorie-Layouts (`product_list`).
- `ruleId = NULL` macht Inhalte für alle B2B-Nutzer im Channel sichtbar (bewusste Konfiguration).
- Nach Admin-Änderungen am Menü-Switch ggf. speichern und `/b2b_platform` neu laden.

---

## Lizenz

MIT — META digital
