# SCHEMA BBDD para personajes

* **Identificador de dominio**: chr_
* **Dominios relacionados**: referencias -> ref_, eventos -> evt_, media_,
* **Tabla principal**: chr_characters

```yaml
schema:
name: character_system
dialect: <sql_dialect> # postgresql | mysql | sqlite | <otro>

tables:

    # =========================
    # TABLA PRINCIPAL
    # =========================
    chr_characters:
      description: >
        Entidad consciente con estructura base común (identidad, clasificación, rol social, núcleo narrativo y mecánica).
      columns:
        id:
          type: uuid
          pk: true
        name:
          type: varchar(255)
          nullable: false
          comment: "Nombre propio / título arcaico / designación / concepto."
        original_universe_id:
          type: uuid
          fk: { table: ref_original_universes, column: id }
          nullable: false
          comment: "Universo de origen de entidad consciente."
        category_id:
          type: uuid
          fk: { table: chr_categories, column: id }
          nullable: false
          comment: "Tipo de entidad consciente."
        subcategory_type_id:
          type: uuid
          fk: { table: chr_subcategory_types, column: id }
          nullable: false
          comment: "Cómo se clasifican los de su mismo tipo (raza, elemento, dominio...)."
        subcategory_definition_id:
          type: uuid
          fk: { table: chr_subcategory_definitions, column: id }
          nullable: false
          comment: "Definición categórica concreta (p.ej. Humano, Fuego, Solar...)."

        is_undead:
          type: boolean
          nullable: false
          default: false
          comment: "Si true, se añade sufijo de no-muerto (ver undead_suffix_id)."
        undead_suffix_id:
          type: uuid
          fk: { table: chr_undead_suf, column: id }
          nullable: true
          comment: "Solo si is_undead=true."
        clone_prefix_id:
          type: uuid
          fk: { table: chr_clone_pre, column: id }
          nullable: true
          comment: "Prefijo si aplica (replica/clon/ia/bot)."
          
        role_id:
          type: uuid
          fk: { table: chr_roles, column: id }
          nullable: false
          comment: "Función social/sistémica."
        hierarchy_id:
          type: uuid
          fk: { table: chr_hierarchies, column: id }
          nullable: false
          comment: "Sistema completo de rangos."
        rank_id:
          type: uuid
          fk: { table: chr_ranks, column: id }
          nullable: false
          comment: "Posición concreta dentro de la jerarquía."
        archetype_id:
          type: uuid
          fk: { table: chr_archetypes, column: id }
          nullable: false
          comment: "Patrón narrativo dominante."

        caste:
          type: boolean
          nullable: false
          comment: "Sí/No (rol heredado o fijo por nacimiento)."
          
        gender:
          type: enum
          nullable: false
          values: [Masculino, Femenino, Indeterminado]

        age_text:
          type: varchar(128)
          nullable: true
          comment: "Edad numérica o simbólica (siglos, eras, desconocida...)."
        age_years:
          type: int
          nullable: true
          comment: "Opcional si age_text no es numérico."

        short_description:
          type: text
          nullable: false
          comment: "1 frase."
        description:
          type: text
          nullable: false
          comment: "2 párrafos."
        large_description:
          type: text
          nullable: false
          comment: "Apariencia física, presencia, lenguaje corporal."
        personality:
          type: text
          nullable: false
          comment: "Rasgos dominantes, contradicciones, límites."
        backstory:
          type: text
          nullable: false
          comment: "Origen, formación, heridas, creencias."

        cone_notes:
          type: text
          nullable: true
          comment: >
            Notas sobre el cono de habilidades (mapa mental/subconsciente). No requiere números; sirve para guía narrativa.

        created_at:
          type: timestamp
          nullable: false
        updated_at:
          type: timestamp
          nullable: false

      indexes:
        - { name: idx_characters_name, columns: [name] }
        - { name: idx_characters_category, columns: [category_id] }

    # =========================
    # MULTIMEDIA (arrays => tabla relacionada)
    # =========================
    chr_media_character:
      description: "Imágenes retrato/portada/tarot frontal/tarot trasera u otros recursos visuales."
      columns:
        id: { type: uuid, pk: true }
        character_id:
          type: uuid
          fk: { table: chr_characters, column: id }
          nullable: false
        media_type:
          type: enum
          nullable: false
          values: [portrait, cover, tarot_front, tarot_back, other]
        url:
          type: text
          nullable: false
          comment: "<url|path>"
        alt_text:
          type: varchar(255)
          nullable: true
        sort_order:
          type: int
          nullable: false
          default: 0

      indexes:
        - { name: idx_media_character, columns: [character_id, media_type] }

    # =========================
    # EVENTOS RELEVANTES (array)
    # =========================
    chr_evt_character_event:
      description: "Ascensos, traiciones, derrotas, pactos; hechos que definen el estado actual."
      columns:
        id: { type: uuid, pk: true }
        character_id:
          type: uuid
          fk: { table: chr_characters, column: id }
          nullable: false
        event_id:
          type: uuid
          fk: { table: evt_events, column: id }
          nullable: true
        title:
          type: varchar(255)
          nullable: false
          comment: "<string>"
        description:
          type: text
          nullable: true
          comment: "<text>"
        happened_at:
          type: date
          nullable: true
          comment: "<date|nullable si es 'era/desconocida'>"
        sort_order:
          type: int
          nullable: false
          default: 0

    # =========================
    # EXPRESIONES RECURRENTES (array)
    # =========================
    chr_expressions:
      description: "Frases, gestos, silencios, tics."
      columns:
        id: { type: uuid, pk: true }
        character_id:
          type: uuid
          fk: { table: chr_characters, column: id }
          nullable: false
        expression:
          type: text
          nullable: false
          comment: "<string>"
        kind:
          type: enum
          nullable: true
          values: [frase, gesto, silencio, tic, otro]

    # =========================
    # MECÁNICA: ATRIBUTOS BASE (1:1)
    # =========================
    chr_stats:
      description: "HP/AD/AP/DEF/MR y contexto de escala."
      columns:
        character_id:
          type: uuid
          pk: true
          fk: { table: chr_characters, column: id }
        hp:
          type: int
          nullable: false
          comment: "0..cap según escala"
        ad:
          type: int
          nullable: false
          comment: "Ataque físico"
        ap:
          type: int
          nullable: false
          comment: "Ataque mágico (si no usa magia: 2; si magia habitual: >=5)"
        def:
          type: int
          nullable: false
          comment: "Defensa física"
        mr:
          type: int
          nullable: false
          comment: "Defensa mágica (si no usa magia: 2; si magia habitual: >=5)"

        stat_cap:
          type: int
          nullable: false
          comment: "235 (estándar) | 2357 (deidad) | 235710 (primigenio)"
        uses_magic:
          type: boolean
          nullable: false
          default: true
          comment: "Si false => AP=2 y MR=2."

    # =========================
    # MECÁNICA: HABILIDADES DEFINIDAS (array)
    # =========================
    chr_abilities:
      description: >
        Habilidades/acciones definidas explícitamente (recomendado: 2,3,5,7). El resto se resuelve por cono.
      columns:
        id: { type: uuid, pk: true }
        character_id:
          type: uuid
          fk: { table: chr_characters, column: id }
          nullable: false
        name:
          type: varchar(255)
          nullable: false
          comment: "<string> (acción o habilidad)"
        affinity_base:
          type: int
          nullable: false
          comment: "0..2357 (23 mínimo funcional; 235 competente estándar)"
        aptitude_type:
          type: enum
          nullable: false
          values: [Fisica, Magica, Cuantica]
          comment: "Cuántica = (Física + Mágica)/2 si aplica."
        cone_hint:
          type: enum
          nullable: true
          values: [Nucleo, ZonaCercana, ZonaMedia, Periferia]
          comment: "Opcional: pista narrativa para ubicarla en el cono."
        notes:
          type: text
          nullable: true

      indexes:
        - { name: idx_abilities_character, columns: [character_id] }

    # =========================
    # TABLAS DE REFERENCIA (catálogos)
    # =========================

    chr_categories:
      description: "Catálogo de categorías."
      columns:
        id: { type: uuid, pk: true }
        name:
          type: varchar(64)
          nullable: false
          unique: true
          comment: >
            Posibles: Personaje, Animal, Elemental, Deidad, Monstruo, Espíritu, Primigenio, Demonio,
            Celestial, Extraplanar, Mutante, Híbrido, Concepto, Colectivo, Avatar

    chr_subcategory_types:
      description: "Tipo de subcategoría por categoría (cómo se clasifican los de su mismo tipo)."
      columns:
        id: { type: uuid, pk: true }
        category_id:
          type: uuid
          fk: { table: chr_categories, column: id }
          nullable: false
        name:
          type: varchar(64)
          nullable: false
          comment: >
            Personaje: raza | Animal: nombre común | Elemental: elemento | Deidad: panteón o dominio |
            Monstruo: tipo de monstruo | Espíritu: tipo de espíritu | Primigenio: origen |
            Demonio: casta | Celestial: coro | Extraplanar: plano de origen | Mutante: tipo de mutación |
            Híbrido: composición | Concepto: dominio | Colectivo: tipo de colmena | Avatar: entidad de origen

    chr_subcategory_definitions:
      description: "Valores concretos por categoría (definición categórica)."
      columns:
        id: { type: uuid, pk: true }
        subcategory_id:
          type: uuid
          fk: { table: chr_subcategory_types, column: id }
          nullable: false
        value:
          type: varchar(128)
          nullable: false
          comment: >
            Ejemplos según categoría:
            - Personaje/raza: Humano, Elfo, Enano, Orco, Híbrido, Mutado, Clonado, Sintético, Consciente
            - Animal/nombre común: Perro, Águila, Halcón, Ballena, Tiburón, Lobo, Oso, Tigre, León
            - Elemental/elemento: Fuego, Agua, Aire, Tierra, Rayo, Hielo, Luz, Sombra, Vacío, Entropía
            - Deidad/dominio: Solar, Lunar, Guerra, Vida, Muerte, Naturaleza, Caos, Orden, Antiguos
            - Monstruo: Bestia, Dragón, Aberración, Bestia mágica, Metamorfo, Planta, Limo, Constructo salvaje
            - Espíritu: Ancestral, Natural, Poltergeist, Banal, Guardián, Vengativo, Kami
            - Primigenio/origen: Cósmico, Abisal, Onírico, Dimensional, Atemporal, Caótico
            - Demonio/casta: Íncubo, Súcubo, Diablillo, Infernal, Señor del Foso, Poseedor, Corruptor
            - Celestial/coro: Ángel, Arcángel, Querubín, Serafín, Deva, Mensajero, Guardián
            - Extraplanar/plano: Astral, Etéreo, Sombra, Espejo, Feérico, Onírico, Elemental de Fuego, ... Entropía
            - Mutante/tipo: Biológico, Mágico, Radiológico, Quimérico, Tecnológico, Psiónico
            - Híbrido/composición: Semiorco, Semielfo, Semiangel, Semidemonio, Quimera
            - Concepto/dominio: Tiempo, Destino, Fortuna, Justicia, Venganza, Amor, Odio
            - Colectivo/tipo: Enjambre, Mente colmena, Consciencia compartida, Red neuronal, Gestalt
            - Avatar/entidad: Encarnación divina, Proyección astral, Holograma sólido, Fragmento de consciencia

    chr_category_undead_suf:
      description: "Sufijos para 'No muerto'."
      columns:
        id: { type: uuid, pk: true }
        value:
          type: varchar(64)
          nullable: false
          unique: true
          comment: "Posibles: esqueleto, zombi, liche, espectro, vampiro, fantasma, cyborg, autómata, constructo"

    chr_category_clone_pre:
      description: "Prefijos para consciencia clon/IA/bot."
      columns:
        id: { type: uuid, pk: true }
        value:
          type: varchar(64)
          nullable: false
          unique: true
          comment: "Posibles: replica, clon, ia, bot"

    # =========================
    # CAMPOS ESPECÍFICOS POR CATEGORÍA (extensible)
    # =========================
    chr_category_fields:
      description: >
        Campos variables por categoría sin romper la estructura base (ej: Personaje->título, Deidad->dominio, Animal->territorio, Elemental->estado).
      columns:
        id: { type: uuid, pk: true }
        character_id:
          type: uuid
          fk: { table: chr_characters, column: id }
          nullable: false
        key:
          type: varchar(64)
          nullable: false
          comment: "Valores típicos: title | domain | territory | manifestation_state | <otro>"
        value:
          type: text
          nullable: false
          comment: "<string>"

      indexes:
        - { name: idx_category_fields_character_key, columns: [character_id, key], unique: true }

    chr_roles:
      description: "Rol social/sistémico."
      columns:
        id: { type: uuid, pk: true }
        name:
          type: varchar(64)
          nullable: false
          unique: true
          comment: "Ejemplos: Arquitecto, Protector, Estratega, Ejecutor, Cazador, Líder, <otro>"

    chr_hierarchies:
      description: "Sistemas de rangos (depende del mundo)."
      columns:
        id: { type: uuid, pk: true }
        name:
          type: varchar(128)
          nullable: false
          unique: true
          comment: "Ejemplos: Manada, Orden militar, Iglesia, Corte divina, Colmena, <otro>"

    chr_ranks:
      description: "Rango concreto dentro de una jerarquía."
      columns:
        id: { type: uuid, pk: true }
        name:
          type: varchar(64)
          nullable: false
          unique: true
          comment: "Ejemplos: Alfa, Subordinado, Capitán, Teniente, <otro>"

    chr_archetypes:
      description: "Arquetipo narrativo dominante."
      columns:
        id: { type: uuid, pk: true }
        name:
          type: varchar(64)
          nullable: false
          unique: true
          comment: "Ejemplos: El líder, El sabio, El traidor, El meditador, El impulsivo, <otro>"

    # (Opcional) catálogo de resultados de acción (0,1,2,3,5,7)
    chr_action_results:
      description: "Interpretación del resultado final de una acción."
      columns:
        id: { type: int, pk: true }
        label:
          type: varchar(64)
          nullable: false
```