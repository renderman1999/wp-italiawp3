# ItaliaWP3

Tema WordPress per la **Pubblica Amministrazione**, derivato da [**ItaliaWP2**](https://italiawp.borisamico.it/v2/) di **Boris Amico**, con personalizzazioni **Open Data PA** a cura di **Asymmetrica**.

- **Repository:** [github.com/renderman1999/wp-italiawp3](https://github.com/renderman1999/wp-italiawp3)
- **Plugin Open Data PA:** [github.com/renderman1999/wp-opendata-pa](https://github.com/renderman1999/wp-opendata-pa)
- **Upstream ItaliaWP2:** [github.com/italia/design-wordpress-theme-italiaWP2](https://github.com/italia/design-wordpress-theme-italiaWP2) — [italiawp.borisamico.it/v2](https://italiawp.borisamico.it/v2/)
- **Contatti:** opendata@asymmetrica.it

---

## Avviso (da [italiawp.borisamico.it](https://italiawp.borisamico.it/v2/))

> L'autore dei template grafici (per piattaforma WordPress) denominati "ItaliaWP" e "ItaliaWP2", liberamente scaricabili, utilizzabili e modificabili, **non e responsabile** dell'uso che gli utilizzatori ne fanno, ne dei contenuti pubblicati sui portali che li adottano.

**ItaliaWP3** e le sue personalizzazioni Open Data sono opera di **Asymmetrica**, distinta da Boris Amico e dall'ente titolare del sito. L'ente e Asymmetrica restano responsabili dei propri contenuti e configurazioni; Boris Amico non risponde dell'uso del derivato salvo quanto previsto dalla licenza GPL sul codice originale.

Il layout si ispira a **Bootstrap Italia** e al **prototipo AgID** per i siti dei Comuni: cio **non implica** approvazione o certificazione AgID del sito.

---

## Modifiche rispetto a ItaliaWP2 (Asymmetrica)

- Catalogo e filtri dataset (`inc/opendata-catalog.php`, `inc/opendata-catalog.js`)
- Template scheda dataset, archivio, slider e categorie dataset
- Integrazione con plugin [Open Data PA](https://github.com/renderman1999/wp-opendata-pa)
- Stili aggiuntivi in `inc/adjustments.css`

Riferimento metadati: [Guida pratica DCAT-AP_IT](https://guida-pratica-dcat-ap-it.readthedocs.io/en/latest/guida.html)

---

## Licenza del tema (GPL-3.0+)

Copyright (c) 2018-2021 and moral rights **Boris Amico** ([borisamico.it](http://borisamico.it)).

Special thanks to **Erik Golinelli**.

Copyright (c) 2025-2026 **Asymmetrica** (opendata@asymmetrica.it) — modifiche ItaliaWP3 / Open Data.

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

See [LICENSE](LICENSE) and [style.css](style.css).

---

## Altre licenze (componenti inclusi)

| Componente | Licenza | Riferimento |
|------------|---------|-------------|
| Bootstrap Italia | BSD-3-Clause | [github.com/italia/bootstrap-italia](https://github.com/italia/bootstrap-italia/blob/master/LICENSE) |
| jQuery | MIT | [jquery.org/license](https://jquery.org/license/) |
| Leaflet | BSD-2-Clause | [leafletjs.com](https://leafletjs.com/) |
| Owl Carousel | MIT | file in `static/js/vendor/` |
| Splide | MIT | file in `static/js/vendor/` |
| TGMPA | GPL-2.0 | [inc/tgm-plugin-activation/LICENSE.md](inc/tgm-plugin-activation/LICENSE.md) |
| Font Lora, Titillium Web | SIL OFL 1.1 | `static/fonts/*/OFL.txt` |
| Font Roboto Mono | Apache 2.0 | `static/fonts/Roboto_Mono/LICENSE.txt` |

---

## Note legali del sito

Modello testuale per la pagina "Note legali" dell'ente: vedi [note-legali-modello.txt](note-legali-modello.txt).

---

## Installazione

```bash
git clone https://github.com/renderman1999/wp-italiawp3.git italiawp2
```

Copia (o clona direttamente) in `wp-content/themes/italiawp2/` — lo **slug cartella** deve restare `italiawp2` (text domain del tema).

1. Copiare la cartella del tema in `wp-content/themes/italiawp2`.
2. Attivare il tema da **Aspetto > Temi**.
3. Configurare **Dettagli**, menu e personalizzazione come per ItaliaWP2 (vedi documentazione upstream).
4. Installare e attivare il plugin **Open Data PA** per catalogo e dataset.

---

## Crediti footer

Il tema mantiene i crediti richiesti da ItaliaWP2 ("Per favore, non rimuoverlo!"): WordPress, tema grafico, derivazione da ItaliaWP2 (Boris Amico), prototipo AgID.
