# Módulo de envíos ANDO

## Requisitos

```
Magento version >= 2.1.2 
```

## Instalación

1. composer require andolatam/magento2:"dev-master"
2. php bin/magento module:enable Improntus_Ando --clear-static-content
3. php bin/magento setup:upgrade
4. rm -rf var/di var/view_preprocessed
5. php bin/magento setup:static-content:deploy

## Autor

* Mauro Maximiliano Martinez - <http://www.improntus.com>

