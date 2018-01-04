# Collector Checkout by Ecomatic

## General Information

The Invoice Fee will not be shown in the cart section on checkout page

Known to work in versions: 2.2.0 and 2.1.8

## Settings

#### Required settings:
All of these are in Stores -> Configuration
* Collector -> Checkout -> General -> Username
* Collector -> Checkout -> General -> iframe Password
* Collector -> Checkout -> General -> Store ID
* Collector -> Checkout -> General -> Terms and conditions URL, full url incluing protocol (e.g https://example.com/terms)
* Sales -> Tax -> Shopping Carty Display Settings -> Display Subtotal -> Including Tax or Including and Excluding Tax
* General -> General -> Store Information -> Country -> Sweden or Norway

#### Recommended Settings:
* Sales -> Tax -> Shopping Carty Display Settings -> Display Prices -> Including Tax
* Sales -> Tax -> Shopping Carty Display Settings -> Display Shipping Amount -> Including Tax
* Sales -> Tax -> Default Tax Destination Calculation -> Default Country -> Same country as store information in Requirered Settings
* Sales -> Shipping Settings -> Origin -> Country -> Same country as store information in Requirered Settings


## Known Issues