# Summa Badge Bundle

[![Version Status](https://img.shields.io/badge/version-1.0-brightgreen.svg)]()


## Description
Module to manage custom badges for different products. This module allows the user to upload a custom badge image, select the position of the badge (top left, top right, middle left, middle right, bottom left, bottom right). 
The badges relationship with products can be done via Backoffice or CSV. Possibility to add a new Rule (same type of rules used for dynamic Price List) to select the products to apply the badge.
Each badge can be scheduled with an expiration date to enable or disable it automatically. 
Also similar to the above the user can configure to apply the badge for N days. For example if the user applies a rule to add a custom badge to all the products with the attribute “newArrival” in TRUE then the badge will be applied for N days to all the products that match the rule.


## Features

- Upload custom badge image
- Set badge position 
- Use Rules to assign bagdes to products
- Schedule badges
- Configure to apply the badge for N days
- Upload badge relationship via CSV

## Install
For install this bundle run this commands.

```php
$ php composer require summa-badge-bundle
$ php bin/console oro:platform:update --force
```
## RoadMap (ToDo)

* [TODO.md](TODO.md)

[![Power by ](https://www.summasolutions.net/wp-content/uploads/2018/11/summa_color.png)](https://www.summasolutions.net)

   