# NumaxLab Lunar Geslib Integration (numaxlab/lunar-geslib)

This package integrates Geslib data with the Lunar e-commerce platform. Geslib appears to be a data management system or format, commonly used by bookstores and publishers, which provides product information via structured "INTER" files.

The primary purpose of `numaxlab/lunar-geslib` is to:
- Process these Geslib INTER files.
- Map the data contained within them (such as articles/books, editorials, authors, topics, etc.) to corresponding Lunar models (e.g., Products, Brands, Collections, Attributes).
- Enable a Lunar-based e-commerce site to reflect the product catalog managed in Geslib.

This package relies on the `numaxlab/geslib-files` library to parse the Geslib INTER file format.

## Installation

**Requirements:**
- PHP ^8.2
- [LunarPHP](https://lunarphp.io/) (core and admin panel)

**Steps:**

1.  **Install the package via Composer:**
    ```bash
    composer require numaxlab/lunar-geslib
    ```
    The package service provider will be auto-discovered by Laravel.

2.  **Run the installation command:**
    ```bash
    php artisan lunar:geslib:install
    ```
    This command will:
    - Publish the configuration file to `config/lunar/geslib.php`. You will be prompted if you want to overwrite it if it already exists.
    - Run necessary database migrations. This package adds a `geslib_inter_files` table to track processed Geslib files.
    - **Set up essential Lunar data (if not already present):**
        - Create a default admin user (if none exists).
        - Import country and currency data (via standard Lunar commands).
        - Create a default webstore channel, 'es' language, EUR currency, and a retail customer group.
    - **Configure Spanish Taxation:**
        - Set up tax classes: "IVA general" (21%), "IVA reducido" (10%), and "IVA superreducido" (4%).
        - Set up a tax zone for "España peninsular + Baleares".
    - **Create Lunar Collection Groups:** These groups are used to categorize Geslib data within Lunar (e.g., "Tipos de artículos" (Article Types), "Idiomas" (Languages), "Autores" (Authors), "Formatos de encuadernación" (Binding Types), etc.).
    - **Create Lunar Attributes:** A comprehensive set of attributes will be created for:
        - **Products (Articles):** Attributes like Title, Subtitle, Creation Date, Novelty Date, Issue Date, Edition Number, Pages, Original Title, etc. These are specifically tailored for book and media information.
        - **Brands (Editorials):** Attributes like Geslib Code, Type (e.g., Editorial, Record Company), Country.
        - **Collections:** Attributes like Geslib Code, Name.
    - **Create a "Geslib Article" Product Type:** This Product Type will be pre-configured with the relevant attributes created above.
    - Publish Filament assets.

## Configuration

After running `lunar:geslib:install`, the configuration file will be available at `config/lunar/geslib.php`. The available options are:

-   **`inter_files_disk`**: (String) Specifies the Laravel filesystem disk where your Geslib INTER files are stored.
    *   Default: `'local'`
    *   Example: You might set this to `'s3'` if you retrieve INTER files from an Amazon S3 bucket.

-   **`inter_files_path`**: (String) The path on the configured disk where the Geslib INTER files can be found.
    *   Default: `'/geslib/inter'`
    *   Example: If your files are in `storage/app/my_geslib_imports`, and `inter_files_disk` is `'local'`, this would be `'my_geslib_imports'`.

-   **`product_types_taxation`**: (Array) A mapping of Geslib's internal product type codes to Lunar Tax Class IDs. The install command pre-populates Lunar with tax classes and uses their IDs here. You may need to adjust these IDs if you have a custom tax setup or if the default IDs (1, 2, 3) are already in use by other tax classes in your Lunar installation.
    *   Default:
        ```php
        [
            'L0' => 3, // Libros (Books) => superreducido (Super-reduced VAT)
            'P0' => 3, // Papelería (Stationery) => superreducido
            'R0' => 3, // Prensa (Press) => superreducido
            'A0' => 1, // Audiovisuales (Audiovisuals) => general (General VAT)
        ]
        ```
    *   The keys are Geslib type codes (e.g., 'L0') and values are the Lunar `tax_classes.id`.

## Usage - Importing Geslib Data

Once the package is installed and configured, you can import data from Geslib INTER files using the following Artisan command:

```bash
php artisan lunar:geslib:import
```

**Process:**

1.  **File Scanning:** The command scans the directory specified by `inter_files_path` on the `inter_files_disk` (from your `config/lunar/geslib.php` file) for any files.
2.  **Tracking Processed Files:** It checks each found file against the `geslib_inter_files` table in your database. This table stores the name and last modification timestamp of files that have already been processed.
3.  **Dispatching Jobs:** For each new or updated file found:
    *   A record is created in the `geslib_inter_files` table.
    *   A `NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile` job is dispatched to the queue.
4.  **Job Execution:** The queued job (`ProcessGeslibInterFile`) performs the actual parsing of the INTER file and the mapping of its data into your Lunar store. It uses the `numaxlab/geslib-files` package to read the file line by line and then employs various internal command classes to handle different types of Geslib records (articles, authors, editorials, etc.).

It's recommended to run this command periodically (e.g., via a scheduled task/cron job) to keep your Lunar catalog synchronized with updates from Geslib.

## Data Mapping Overview

This package translates data from Geslib INTER files into various Lunar e-commerce models. Here's a general overview:

-   **Geslib Articles (Books, etc.) → Lunar Products & Product Variants:**
    -   The core product information from Geslib (like title, subtitle, ISBN, EAN, publication dates, pages, dimensions, weight, stock levels, and price) is mapped to Lunar Product attributes (many of which are custom-created by the `lunar:geslib:install` command) and Product Variant details.
    -   The Geslib Article ID (often an ISBN or internal code) typically becomes the SKU for the Lunar Product Variant.
    -   Each product is assigned a Lunar Brand, which corresponds to the Geslib Editorial (publisher/label).
    -   Products are also linked to Lunar Collections to represent their Geslib Language and Geslib Product Type (e.g., Book, Stationery).

-   **Geslib Editorials → Lunar Brands:**
    -   Information about publishers or record labels from Geslib is mapped to Lunar Brands.
    -   Attributes associated with these brands include the original Geslib code and the type of entity (e.g., "Editorial", "Discográfica" (Record Company)).

-   **Geslib Authors, Topics, Geslib Collections, Languages, Binding Types, Classifications → Lunar Collections:**
    -   Many categorical or relational entities from Geslib are represented as Lunar Collections. These are typically grouped under the Collection Groups created during installation (e.g., a "Languages" collection group will contain individual language collections like "Spanish", "English", based on Geslib data).
    -   This structure allows products to be easily filtered and organized by these criteria in your Lunar store. For example, a book can be associated with its author(s), its language, its topics, and its binding type through these collections.

-   **Geslib Stock → Lunar Product Variant Stock:**
    -   Stock quantities provided in Geslib files update the stock levels of the corresponding Lunar Product Variants.

-   **Geslib Product Types → Lunar Product Tax & Collections:**
    -   Geslib product types (e.g., 'L0' for book, 'A0' for audiovisual) are used to determine the appropriate tax class for the Lunar product (as per the `product_types_taxation` config).
    -   They also map to specific Lunar Collections under the "Tipos de artículos" (Article Types) collection group.

The `lunar:geslib:install` command creates a rich set of custom attributes in Lunar, specifically designed to hold the detailed information typically found in Geslib data for books and other media. The import process then populates these attributes.

## Geslib INTER File Processing Details

The `ProcessGeslibInterFile` job is responsible for parsing the content of a Geslib INTER file. This job utilizes the `numaxlab/geslib-files` library, which understands the specific structure and line codes of these files.

Each line in a Geslib INTER file starts with a code indicating the type of data it represents. The import process maps these codes to specific handler commands within this package. Here are some of the primary Geslib line codes processed:

-   **`ART` (Article):** Maps to Lunar Products and Product Variants. Includes details like title, ISBN, EAN, price, stock, dimensions, dates, etc.
-   **`AUT` (Author):** Creates/updates Lunar Collections representing authors, grouped under "Autores". Products are then linked to these author collections.
-   **`EDI` (Editorial):** Maps to Lunar Brands, representing publishers.
-   **`COL` (Collection - Editorial Collection):** Creates/updates Lunar Collections for editorial series/collections, grouped under "Colecciones editoriales".
-   **`IDI` (Language):** Creates/updates Lunar Collections for languages, grouped under "Idiomas". Products are linked to their respective language.
-   **`MAT` (Topic/Subject):** Creates/updates Lunar Collections for topics or subjects, grouped under "Materias". Products are linked to these.
-   **`TIP` (Type - Product Type):** Creates/updates Lunar Collections for product types (e.g., book, stationery), grouped under "Tipos de artículos". Products are linked, and this also influences tax calculation.
-   **`ENC` (Binding Type):** Creates/updates Lunar Collections for binding types, grouped under "Formatos de encuadernación".
-   **`CLA` (Classification):** Creates/updates Lunar Collections for classifications, grouped under "Clasificaciones".
-   **`EAN` (Stock):** Primarily updates stock levels for existing Product Variants based on EAN/barcode.
-   **`DIS` (Record Label):** Maps to Lunar Brands, similar to Editorials but for music/audio. (Handled by `RecordLabelCommand`)
-   **`PUB` (Press Publication):** Can create specific brands or product types for press publications.
-   **`REF_LIB` (Bookshop Reference):** Can add bookshop-specific reference information to products.
-   **`REF_EDI` (Editorial Reference):** Can add editorial-specific reference information to products.
-   **`IND_ART` (Article Index):** Can add index information to product attributes.
-   **`ART_AUT` (Article-Author Link):** Establishes the many-to-many relationship between products (articles) and authors (collections).
-   **`ART_MAT` (Article-Topic Link):** Establishes the relationship between products and their topics/subjects.

Several other Geslib line codes are also processed to create a comprehensive data model in Lunar. Some codes might be explicitly ignored if they are not relevant to the e-commerce presentation or are handled by other lines. If you encounter issues with specific Geslib data not appearing as expected, reviewing the `NumaxLab\Lunar\Geslib\Jobs\ProcessGeslibInterFile::handle()` method can provide insight into which line codes are actively processed.

## License

This package is open-source software licensed under the [MIT license](LICENSE.md).

## Authors

This package is developed by:
- Adrián Pardellas Blunier ([adrian@numax.org](mailto:adrian@numax.org))
- Noa García Amado ([noa@numax.org](mailto:noa@numax.org))

Maintained by [Numax Lab](https://numax.org/).
