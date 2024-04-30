# Servicio de Facturación Electrónica Laravel

<!-- ![IMAGES DE GO LANG](images/ladder.svg) -->
<img src="public/images/logo.png" alt="Imagen SysSoft Integra" width="200" />


## Iniciar

Este proyecto utiliza Laravel para declarar comprobantes electrónicos. A continuación, se proporciona información sobre cómo configurar y ejecutar la aplicación.

Algunos recursos para iniciar con este proyecto puedes ver en:

- [Php](https://www.php.net/) PHP es un lenguaje de programación interpretado​ del lado del servidor y de uso general que se adapta especialmente al desarrollo web.

- [Composer](https://getcomposer.org/) Composer es un sistema de gestión de paquetes para programar en PHP el cual provee los formatos estándar necesarios para manejar dependencias y librerías de PHP.

- [Laravel](https://laravel.com/) Laravel es un framework de código abierto para desarrollar aplicaciones y servicios web con PHP 5, PHP 7 y PHP 8.

- [Visual Studio](https://code.visualstudio.com/) Editor de código para todos tipos de lenguaje de programación.

- [Git](https://git-scm.com/) Software de control de versiones.

- [Git Hub](https://github.com/) Plataforma de alojamiento de proyecto de todo ámbito.

## Instalación

Siga los pasos para iniciar el desarrollo:

### 1.  Clona el proyecto o agrague el ssh al repositorio para contribuir en nuevos cambios [Git Hub - Facturación Electrónica](https://github.com/luissince/syssoft-integra-cpe-sunat)

#### 1.1. Agregue por ssh para la integración

Generar tu clave ssh para poder contribuir al proyecto.

```bash
ssh-keygen -t rsa -b 4096 -C "tu email"
```

Configuración global del nombre.

```bash
git config --global user.name "John Doe"
```

Configuración global del email.

```bash
git config --global user.email johndoe@example.com
```

Crea una carpeta.

```bash
mkdir syssoft-integra-cpe-sunat
```

Moverse a la carpeta.

```bash
cd syssoft-integra-cpe-sunat
```

Comando para inicia git.

```bash
git init
```

Comando que agrega la referencia de la rama.

```bash
git remote add origin git@github.com:luissince/syssoft-integra-cpe-sunat.git
```

Comando que descarga los archivos al working directory.

```bash
git fetch origin master
```

Comando que une los cambios al staging area.

```bash
git merge origin/master
```

#### 1.2 Clonar al proyecto

Al clonar un proyecto no necesitas crear ninguna carpeta.

```bash
git clone https://github.com/luissince/syssoft-integra-cpe-sunat.git
```

### 2. Instale php desde la págin oficial

```bash
https://www.php.net/downloads
```

### 3. Instale las dependencias

```bash
composer install
```

### 4. Generar una clave de aplicación única

```bash
php artisan key:generate
```

### 5. Configuración de Variables de Entorno

A continuación, se presenta la configuración de las variables de entorno utilizadas:

```bash
APP_DEBUG=true
APP_ENV="local"
```

### 6. Ejecute el siguiente comando para ejecutar en modo desarrollo

```bash
php artisan serve --host=0.0.0.0 --port=9000
```

### 7. Configuración para Ejecutar GitHub Actions para el CI/CD:

Para ejecutar los workflows de GitHub Actions, asegúrate de que tu usuario tenga los privilegios de ejecución necesarios. A continuación, te proporcionamos algunos pasos para empezar:


#### 7.1. Crea un grupo de Docker:

```bash
sudo groupadd docker
```

#### 7.2. Agrega tu Usuario al Grupo de Docker:

```bash
sudo usermod -aG docker $USER
```

#### 7.3. Aplica los Cambios en el Grupo de Docker:

```bash
newgrp docker
```

#### 7.4. Verifica que tu Usuario esté en el Grupo de Docker:

```bash
groups
```
Asegúrate de que "docker" esté en la lista de grupos.

#### 7.5. Configuración y Uso del Runner:

Para iniciar la creación del runner, ve a Settings del proyecto, luego a Actions, Runners, y selecciona "New self-hosted runner".

Si deseas ejecutar en segundo plano, utiliza los siguientes comandos de configuración:

```bash
sudo ./svc.sh status
sudo ./svc.sh install
sudo ./svc.sh start
sudo ./svc.sh stop
sudo ./svc.sh uninstall
```

Estos comandos te permiten controlar el runner según sea necesario.

### 8. Punto importante la hacer git push

Cuando realices un git push origin master y desees evitar que se ejecute el flujo de trabajo de GitHub Actions, puedes incorporar [skip ci] o [ci skip] en el mensaje del commit. Esta adición indicará a GitHub Actions que omita la ejecución de los trabajos para ese commit específico.

Por ejemplo, al realizar un commit, puedes utilizar el siguiente comando para incluir [skip ci] en el mensaje del commit:

```bash
git commit -m "Tu mensaje del commit [skip ci]"
```

### 9. Punto importante al hacer al hacer commit

Si deseas mantener mensajes de commit distintos para desarrollo, prueba y producción, pero sin tener que hacer un commit en la rama de desarrollo antes de probar en la rama de prueba, puedes utilizar la opción --no-ff (no fast-forward) al realizar la fusión en cada rama. Esto te permitirá realizar un commit específico en la rama de prueba (y posteriormente en la rama de producción) incluso si no hubo cambios adicionales en desarrollo.

1. En la rama desarrollo

```bash
git checkout desarrollo
git pull origin desarrollo
# Realiza tus cambios y realiza el commit
git add .
git commit -m "Mensaje de desarrollo"
```

2. Cambia a la rama de prueba

```bash
git checkout test
git pull origin test
# Fusiona los cambios de desarrollo con un commit específico
git merge --no-ff desarrollo -m "Mensaje de prueba"
```

El uso de --no-ff asegurará que se cree un nuevo commit, incluso si no hubo cambios adicionales en desarrollo.