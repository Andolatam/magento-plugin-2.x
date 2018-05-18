# Módulo de envíos ANDO

## Requisitos

```
Magento version >= 2.1.2 
```

## Instalación


1. php bin/magento module:enable Improntus_Ando --clear-static-content
2. php bin/magento setup:upgrade
3. rm -rf var/di var/view_preprocessed
4. php bin/magento setup:static-content:deploy

## Autor

* Mauro Maximiliano Martinez - <http://www.improntus.com>

