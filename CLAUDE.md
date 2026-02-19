# CLAUDE.md — PrecisionLedBox Showroom PL2

## Project Overview

This is a **Home Assistant configuration** for the PrecisionLedBox commercial showroom (PL2). It is entirely focused on LED lighting control — no climate, presence, or security integrations. All logic revolves around:

- **Ambiances** — static color scenes per holiday/season
- **Animations** — looping dynamic sequences per theme
- **Circadian cycle** — automatic color temperature and brightness based on time of day
- **Boost mode** — manual and scheduled attention-grabbing red flash
- **IA mode** — calendar-driven auto-selection of the appropriate theme

The entire configuration is written in **French**.

---

## File Structure

```
configuration.yaml          # Main config: packages, themes, sensors, helpers, groups
automations.yaml            # All automation logic (~893 lines)
scripts.yaml                # set_mode_lumieres utility script
scenes.yaml                 # Empty (unused)
secrets.yaml                # Credentials (never edit directly)
packages/
  lighting_boost.yaml       # Boost-related input helpers (input_boolean, input_select, etc.)
scripts/
  lighting_boost_run.yaml   # Full boost script with mode save/restore
lighting/
  README.md                 # Full system documentation — read before making changes
  ambiances/                # 10 static ambiance sequence files (one per theme)
  animations/               # 11 looping animation sequence files (one per theme)
  palettes/                 # 7 color palette definition files
  presets.yaml              # Placeholder (empty)
  ia_chooser.yaml           # Placeholder (empty)
blueprints/                 # Standard HA built-in blueprints only
custom_components/hacs/     # HACS integration
themes/mushroom/            # 4 Mushroom theme variants
www/community/              # Mushroom cards + better-sliders (via HACS)
```

---

## Architecture

### Lighting Modes

All modes are `input_boolean` entities. Only **one mode can be active at a time** — mutual exclusion is enforced at three levels:

1. The master dispatcher automation (`automations.yaml`) uses `!include` to call the correct ambiance/animation file.
2. Exclusion automations turn off all other modes when a new one activates.
3. The `set_mode_lumieres` script turns off all 15 mode booleans before activating the requested one.

**Naming convention:**
- `input_boolean.mode_<theme>` — static ambiance (e.g. `mode_noel`, `mode_halloween`)
- `input_boolean.modeanim_<theme>` — animation loop (e.g. `modeanim_noel`, `modeanim_disco`)

### Priority Hierarchy

```
Boost Manual > Boost Planifié > Ambiance/Animation > Circadian Cycle
```

### Circadian Cycle

Seven phases (defined in `configuration.yaml` as template sensors):

| Phase | Hours | Kelvin | Brightness |
|---|---|---|---|
| Reveil progressif | 06:00–09:00 | 3000 K | 50% |
| Matinee energisante | 09:00–12:00 | 4500 K | 80% |
| Midi dynamique | 12:00–14:00 | 5500 K | 100% |
| Apres-midi concentre | 14:00–17:00 | 5000 K | 90% |
| Soiree transition | 17:00–20:00 | 3500 K | 60% |
| Pre-sommeil | 20:00–22:00 | 2700 K | 30% |
| Nuit repos | 22:00–06:00 | 2200 K | 10% |

Applied every 5 minutes AND on phase change, with 10 guard conditions ensuring no ambiance/animation/boost is active.

### IA Mode

Pure Jinja2 calendar logic inside `automations.yaml`. Selects a seasonal theme based on `now().month` / `now().day`, writes the label to `input_text.ia_choix_ambiance` and `input_text.ia_choix_animation`, and re-evaluates every 10 minutes. Extra IA-only themes (12 seasonal variants) are dispatched via a single `extra.yaml` file using a `variables: { extra_mode: "..." }` pattern.

### Boost Mode

- **Manual**: Loops 3s red / 1s off while `input_boolean.boost_manual_override` is on.
- **Scheduled**: Fires at `input_datetime.boost_time`, flashes for `input_number.boost_duration_min` minutes at `input_number.boost_interval_sec` intervals.
- **Boost color**: Configurable via `input_select.boost_color` (Rouge, Bleu, Vert, Rose, Violet, Orange).
- **State preservation**: `lighting_boost_run` saves the active mode to `input_text.boost_previous_mode` and restores it on completion.

---

## Hardware

- **Zigbee bulbs**: `light.tzb210_1alquo2j_ts0505b_2` through `..._14` (13 individual bulbs)
- **LED strip**: `light.ruban_led`
- **Large spotlights**: `light.gros_spot_1` through `light.gros_spot_4`
- **Logical zone groups**: `light.zone_1` through `light.zone_4`
- **Zigbee coordinator**: confirmed by `zigbee.db`

---

## Key Themes

| Theme | Type | Description |
|---|---|---|
| `noel` | ambiance + animation | Christmas: green/red zones |
| `halloween` | ambiance + animation | Orange/purple zones |
| `pride` | ambiance + animation | Rainbow zones |
| `ice` | ambiance + animation | Ice-blue + cold white |
| `octobrerose` | ambiance + animation | Pink/fuchsia |
| `urbans` | ambiance + animation | Natural greens (default fallback) |
| `disco` | animation only | Fast festive colors |
| IA extras | ambiance + animation | 12 seasonal/holiday variants |

---

## Adding a New Theme

1. Create `lighting/ambiances/<theme>.yaml` — static zone color assignments.
2. Create `lighting/animations/<theme>.yaml` — looping `while:` sequence with 5s delays and transitions.
3. Add a palette file `lighting/palettes/<theme>.yaml` if introducing new colors.
4. Add `input_boolean.mode_<theme>` and/or `input_boolean.modeanim_<theme>` to `configuration.yaml`.
5. Add the new boolean(s) to the `modes_lumieres` group in `configuration.yaml`.
6. Add a `choose:` branch in the dispatcher automation in `automations.yaml` that `!include`s the new files.
7. Add the boolean(s) to the exclusion lists in all mutual-exclusion automations.
8. Add the boolean(s) to the `set_mode_lumieres` script's turn-off list in `scripts.yaml`.

---

## Known Issues / Watch Out

- **Duplicate boost helpers**: Boost-related `input_boolean`, `input_select`, `input_datetime`, and `input_number` entries exist in **both** `configuration.yaml` and `packages/lighting_boost.yaml`. This would cause a conflict in a live HA instance and should be reconciled (remove from `configuration.yaml` or from the package).
- `lighting/presets.yaml` and `lighting/ia_chooser.yaml` are empty placeholders — not yet implemented.
- `scenes.yaml` is empty — all visual presets are handled via ambiance sequences, not HA scenes.

---

## Configuration Patterns

- **`!include`** — used for ambiance/animation sequence files from automations
- **`!include_dir_merge_named`** — used for packages and themes
- **`!include_dir_named`** — used for packages directory
- Zigbee device IDs follow the pattern `light.tzb210_1alquo2j_ts0505b_N`
- All entity IDs, automation aliases, and script names are in **French**

---

## Frontend

- **Mushroom cards** (via HACS): `www/community/lovelace-mushroom/`
- **Better Sliders** extension: `www/community/lovelace-mushroom-better-sliders/`
- Theme variants in `themes/mushroom/` (4 color variants)

---

## HTTP / Network

- Reverse proxy configured in `configuration.yaml`
- Trusted proxy range: `172.30.33.0/24`


# Infos supplémentaires

Objectifs:
- eviter la duplication
- permettre un pilotage manuel et IA
- faciliter l'ajout de nouveaux themes
- garder une maintenance lisible dans le temps

## Regle globale
- Un seul mode lumiere peut etre actif a la fois (ambiance, animation ou IA).
- Si un mode s'active, tous les autres modes lumiere sont coupes.
- Le boost manuel est prioritaire et coupe les autres modes.

## Ambiances manuelles (fixes)
- `Noel`: rouge + vert (esprit fetes).
- `Halloween`: orange + violet + rouge.
- `Octobre Rose`: nuances de rose/fuchsia.
- `Pride`: multicolore.
- `Urban's`: ambiance verte/nature.
- `Ice`: bleu glace + blanc froid.

## Animations manuelles (dynamiques)
- `Noel`: alternance rouge/vert entre zones.
- `Halloween`: alternance orange/violet/rouge.
- `Octobre Rose`: rotation de roses/fuchsia.
- `Pride`: rotation multicolore.
- `Urban's`: alternance verte douce.
- `Ice`: vagues bleu glace/blanc froid.
- `Disco`: animation festive rapide.

## Mode IA (Ambiances + Animations)
L'IA choisit automatiquement selon le calendrier et ecrit un label explicatif (`input_text.ia_choix_ambiance` et `input_text.ia_choix_animation`).

Priorites actuelles:
- `26/12 -> 05/01`: Hiver - Fetes de fin d'annee.
- `01/01 -> 07/01`: Nouvel An.
- `13/02 -> 20/02`: Saint-Valentin.
- `20/03 -> 24/03`: Journee de l'eau.
- `19/03 -> 27/03`: Arrivee du printemps.
- `20/04 -> 24/04`: Journee de la Terre.
- `19/06 -> 27/06`: Arrivee de l'ete.
- `29/06 -> 03/07`: Canada Day.
- `19/07 -> 23/07`: Fete nationale belge.
- `28/08 -> 10/09`: Rentree.
- `20/09 -> 28/09`: Arrivee de l'automne.
- `01/11 -> 30/11`: Movember.
- Sinon: `Ice` (janvier/fevrier), `Noel` (decembre), `Halloween` (novembre), `Octobre Rose` (octobre), puis `Urban's` par defaut.

Les themes IA supplementaires sont centralises dans:
- `lighting/ambiances/extra.yaml`
- `lighting/animations/extra.yaml`

## Boost
- `Boost manuel`: boucle fixe 3 secondes rouge plein, puis 1 seconde eteint.
- `Boost manuel`: coupe cycle circadien + ambiances + animations pendant l'activation.
- `Boost planifie`: demarrage a `input_datetime.boost_time` si active.
- `Boost planifie`: clignotement rouge ON/OFF avec intervalle configurable (`input_number.boost_interval_sec`).
- `Boost planifie`: duree configurable (`input_number.boost_duration_min`).

## Cycle circadien
Quand actif, il applique automatiquement temperature et luminosite selon l'heure.

Phases:
- `06:00-09:00`: Reveil progressif -> 3000K, 50%.
- `09:00-12:00`: Matinee energisante -> 4500K, 80%.
- `12:00-14:00`: Midi dynamique -> 5500K, 100%.
- `14:00-17:00`: Apres-midi concentre -> 5000K, 90%.
- `17:00-20:00`: Soiree transition -> 3500K, 60%.
- `20:00-22:00`: Pre-sommeil -> 2700K, 30%.
- `22:00-06:00`: Nuit repos -> 2200K, 10%.

## Bonnes pratiques
- Ne pas mettre `alias`, `trigger`, `condition` dans les fichiers inclus de sequences.
- Un fichier = une responsabilite.
- Les decisions de priorite/date restent dans `automations.yaml`.

## Fichiers utiles
- Logique principale: `automations.yaml`
- Sequences fixes: `lighting/ambiances/*.yaml`
- Sequences dynamiques: `lighting/animations/*.yaml`
- Themes IA supplementaires: `lighting/ambiances/extra.yaml`, `lighting/animations/extra.yaml`
