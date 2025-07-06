# ML Mailing Lists

Plugin de WordPress para xestionar listas de correo e subscricións con funcionalidades avanzadas de envío masivo e exportación.

## 📋 Características

- ✅ **Xestión de listas de correo** mediante taxonomías personalizadas
- ✅ **Shortcode de subscrición** personalizable e responsive
- ✅ **Envío masivo de correos** con editor WYSIWYG
- ✅ **Variables de personalización** nos emails
- ✅ **Exportación de datos** en CSV e TXT
- ✅ **Sistema anti-spam** con honeypot e rate limiting
- ✅ **Interface en galego** tanto para frontend como backend
- ✅ **Vista previa** antes do envío masivo
- ✅ **Logging de actividade** de envíos
- ✅ **Seguridade reforzada** con nonces e validacións

## 🚀 Instalación

1. Descarga o plugin
2. Sube a carpeta `ml-mailing-lists` ao directorio `/wp-content/plugins/`
3. Activa o plugin desde o panel de administración de WordPress
4. Configura as listas de correo en **ML Mailing Lists > ML Lista**

## 📖 Uso do Shortcode

### Sintaxe básica

```php
[ml_subscription_form list_id="123"]
```

### Sintaxe completa con todos os parámetros

```php
[ml_subscription_form
    list_id="123"
    title="Subscríbete á nosa newsletter"
    btn_text="Subscribirse agora"
    css_class="ml-subscription-form"
]
```

### Parámetros dispoñibles

| Parámetro | Descrición | Valor por defecto |
|-----------|------------|-------------------|
| `list_id` | **Obrigatorio**. ID da lista de correo | - |
| `title` | Título que aparece no formulario | "Subscríbete á nosa lista" |
| `btn_text` | Texto do botón de envío | "Subscribirse" |
| `css_class` | Clase CSS personalizada | "ml-subscription-form" |

### Exemplos de uso

**Formulario básico:**
```php
[ml_subscription_form list_id="1"]
```

**Formulario personalizado:**
```php
[ml_subscription_form
    list_id="2"
    title="Únete á nosa comunidade"
    btn_text="Subscríbeme xa!"
    css_class="mi-formulario-personalizado"
]
```

## 📧 Envío Masivo de Correos

### Acceso á funcionalidade

1. Ve a **ML Mailing Lists > Enviar Correo**
2. Selecciona a lista de destinatarios
3. Configura os datos do remitente
4. Escribe o asunto e contido
5. Opcionalmente, envía unha vista previa
6. Confirma o envío masivo

### Variables de personalización

O plugin soporta as seguintes variables no contido dos emails:

| Variable | Descrición |
|----------|------------|
| `{{nome}}` | Nome do subscritor |
| `{{apelido}}` | Apelido do subscritor |
| `{{correo}}` | Correo electrónico do subscritor |

### Exemplo de email personalizado

```html
<h2>Ola {{nome}}!</h2>
<p>Grazas por subscribirte á nosa lista con o correo {{correo}}.</p>
<p>Saúdos,<br>
O equipo de {{nome}} {{apelido}}</p>
```

## 📊 Exportación de Datos

### Formatos dispoñibles

- **CSV**: Formato compatible con Excel e follas de cálculo
- **TXT**: Formato de texto plano lexible

### Como exportar

1. Ve a **ML Mailing Lists** (listado principal)
2. Opcionalmente, filtra por unha lista específica
3. Fai clic en **📊 Exportar CSV** ou **📄 Exportar TXT**
4. O arquivo descargarase automaticamente

### Datos incluídos na exportación

- Nome
- Apelido
- Correo electrónico
- Data de subscrición
- Listas ás que pertence

## 🔧 Configuración Avanzada

### Personalización do CSS

O plugin inclúe CSS por defecto, pero podes personalizalo usando as variables CSS:

```css
:root {
    --ml-primary-color: #a-túa-cor-principal;
    --ml-secondary-color: #a-túa-cor-secundaria;
    --ml-background-color: #cor-de-fondo;
    --ml-border-color: #cor-de-borde;
}
```

### Hooks e filtros dispoñibles

**Filtros de personalización:**
```php
// Personalizar datos antes de gardar a subscrición
add_filter( 'ml_subscription_name', 'mi_filtro_nome', 10, 2 );
add_filter( 'ml_subscription_surname', 'mi_filtro_apelido', 10, 2 );
add_filter( 'ml_subscription_email', 'mi_filtro_email', 10, 2 );
```

**Accións de eventos:**
```php
// Executar código despois de crear unha subscrición
add_action( 'ml_subscription_created', 'mi_funcion_post_subscripcion', 10, 3 );

// Activación/desactivación do plugin
add_action( 'ml_plugin_activated', 'mi_funcion_activacion' );
add_action( 'ml_plugin_deactivated', 'mi_funcion_desactivacion' );
```

**Hooks de inicialización:**
```php
// Despois da inicialización das clases
add_action( 'ml_core_initialized', 'mi_funcion_inicializacion' );

// Antes de procesar un formulario
add_action( 'ml_before_form_processing', 'mi_funcion_pre_formulario' );
```

### Exemplos de extensión

**Engadir validación personalizada:**
```php
add_filter( 'ml_subscription_email', function( $email, $list_id ) {
    // Bloquear dominios específicos
    $blocked_domains = ['example.com', 'spam.com'];
    $domain = substr(strrchr($email, '@'), 1);

    if (in_array($domain, $blocked_domains)) {
        return false; // Esto activará unha validación de erro
    }

    return $email;
}, 10, 2 );
```

**Logging personalizado:**
```php
add_action( 'ml_subscription_created', function( $post_id, $email, $list_id ) {
    // Enviar notificación por Slack, Discord, etc.
    $list_name = get_term( $list_id, 'ml_lista' )->name;

    error_log( "Nova subscrición en '{$list_name}': {$email}" );

    // Ou enviar webhook
    wp_remote_post( 'https://hooks.slack.com/services/...', [
        'body' => json_encode([
            'text' => "Nova subscrición: {$email} en {$list_name}"
        ])
    ]);
}, 10, 3 );
```

## 🛡️ Características de Seguridade

### Protección anti-spam

- **Honeypot**: Campo oculto que detecta bots
- **Rate limiting**: Máximo 3 intentos por IP por hora
- **Nonces**: Verificación de tokens de seguridade
- **Validación de datos**: Sanitización e validación estricta

### Permisos de usuario

- Só usuarios con capacidade `manage_options` poden:
  - Enviar correos masivos
  - Exportar datos
  - Acceder ás funcionalidades de administración

## 📁 Estrutura de Arquivos con Namespaces

### Estrutura modular do plugin

```
ml-mailing-lists/
├── ml-mailing-lists.php           # Arquivo principal - Cargador do plugin
├── README.md                      # Documentación completa
└── includes/                      # Classes modulares con namespace ML_Mailing_Lists
    ├── class-core.php          # Core - Xestor principal de dependencias
    ├── class-shortcode.php     # Shortcode - Xestión de formularios
    ├── class-security.php      # Security - Sistema de seguridade
    ├── class-admin.php         # Admin - Interface de administración
    ├── class-email-sender.php  # Email_Sender - Xestión de envío
    ├── class-export.php        # Export - Sistema de exportación
    └── functions.php              # Funcións auxiliares con namespace
```

### Estrutura con namespaces

Todas as clases están baixo o namespace `ML_Mailing_Lists` para evitar conflitos:

```php
namespace ML_Mailing_Lists;

// Inicialización do plugin
\ML_Mailing_Lists\Core::get_instance();

// Acceso ás clases
\ML_Mailing_Lists\Shortcode::get_instance();
\ML_Mailing_Lists\Security::get_instance();
\ML_Mailing_Lists\Admin::get_instance();
\ML_Mailing_Lists\Email_Sender::get_instance();
\ML_Mailing_Lists\Export::get_instance();
```

### Descrición das clases

#### 🔧 `ML_Mailing_Lists\Core` (class-core.php)
- **Función principal**: Cargador e inicializador do plugin
- **Patrón**: Singleton con namespace
- **Responsabilidades**:
  - Cargar todas as dependencias
  - Inicializar as clases modulares
  - Xestionar hooks de activación/desactivación
  - Cargar traduccións

#### 📝 `ML_Mailing_Lists\Shortcode` (class-shortcode.php)
- **Función principal**: Xestión de formularios de subscrición
- **Patrón**: Singleton con namespace
- **Responsabilidades**:
  - Rexistrar e procesar shortcodes
  - Xerar HTML dos formularios
  - Procesar envíos de subscrición
  - Aplicar estilos CSS

#### 🛡️ `ML_Security` (class-security.php)
- **Función principal**: Sistema de seguridade integral
- **Patrón**: Singleton con métodos estáticos
- **Responsabilidades**:
  - Xestión de nonces de seguridade
  - Rate limiting (control de frecuencia)
  - Detección de honeypot anti-spam
  - Validación e sanitización de datos
  - Obtención segura de IP de usuario

#### ⚙️ `ML_Admin` (class-admin.php)
- **Función principal**: Interface de administración
- **Patrón**: Singleton
- **Responsabilidades**:
  - Páxinas de envío masivo
  - Interface de exportación
  - Xestión de menús de admin
  - Procesamento de formularios de admin

#### 📧 `ML_Email_Sender` (class-email-sender.php)
- **Función principal**: Sistema de envío de emails
- **Patrón**: Singleton
- **Responsabilidades**:
  - Envío de emails individuais
  - Envío masivo con personalización
  - Xestión de variables de plantilla
  - Estatísticas de envío

#### 📊 `ML_Export` (class-export.php)
- **Función principal**: Exportación de datos
- **Patrón**: Singleton
- **Responsabilidades**:
  - Exportación en formato CSV
  - Exportación en formato TXT
  - Validación de parámetros
  - Estatísticas de exportación

#### 🔧 Funcións auxiliares (functions.php)
- **Función principal**: Utilidades globais
- **Funcións principais**:
  - `ml_subscription_exists()`: Verificar subscricións existentes
  - `ml_get_subscriber_by_email()`: Obter datos por email
  - `ml_get_list_subscribers()`: Obter subscriptores de lista
  - `ml_get_list_stats()`: Estatísticas das listas
  - `ml_log_activity()`: Sistema de logging
  - `ml_format_date()`: Formateo de datas
  - `ml_user_can_manage_lists()`: Verificación de permisos

## �️ Arquitectura Técnica

### Patrón de deseño implementado

O plugin segue unha **arquitectura modular baseada no patrón Singleton** que garante:

- **Unha soa instancia** de cada clase principal
- **Carga eficiente** de recursos
- **Separación clara** de responsabilidades
- **Fácil mantemento** e extensibilidade

### Fluxo de inicialización

```
WordPress carga → ml-mailing-lists.php → ML_Core::get_instance()
                                           ↓
                                      Carga dependencias
                                           ↓
                    ┌─────────────────────────────────────────┐
                    │          ML_Core::init_plugin()        │
                    └─────────────────────────────────────────┘
                                           ↓
    ┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
    │   ML_Security   │  ML_Shortcode   │  ML_Email_Sender │    ML_Export    │
    │ ::get_instance()│ ::get_instance()│ ::get_instance() │ ::get_instance()│
    └─────────────────┴─────────────────┴─────────────────┴─────────────────┘
                                           ↓
                              ┌─────────────────────────┐
                              │       ML_Admin         │
                              │   ::get_instance()     │
                              │   (só en admin)        │
                              └─────────────────────────┘
```

### Melloras de rendemento

- **Lazy loading**: As clases cárganse só cando se necesitan
- **Singleton pattern**: Evita instanciación múltiple
- **Conditional loading**: ML_Admin só se carga no backend
- **Optimización de queries**: Uso eficiente de meta_query e tax_query
- **CSS estático**: Evita duplicación de estilos

## �🌐 Idiomas

O plugin está completamente traducido ao **galego** tanto no frontend como no backend, incluíndo:

- Formularios de subscrición
- Mensaxes de erro e confirmación
- Interface de administración
- Botóns e labels
- Mensaxes do sistema

## ⚙️ Requisitos Técnicos

- **WordPress**: 5.0 ou superior
- **PHP**: 7.4 ou superior
- **Pods Plugin**: Requerido para a xestión de CPT e taxonomías

## 🔧 Configuración Inicial

### 1. Crear listas de correo

1. Ve a **Listas de correo > Listas**
2. Engade unha nova lista
3. Anota o ID da lista para usar no shortcode

### 2. Configurar o shortcode

Usa o ID da lista no teu shortcode:
```php
[ml_subscription_form list_id="O_TEU_ID_DE_LISTA"]
```

### 3. Configurar o envío de emails

1. Verifica a configuración de email de WordPress
2. Considera usar un plugin de SMTP para mellor entregabilidade
3. Proba o envío coa función de vista previa

## 📈 Rendemento e Escalabilidade

### Optimizacións incluídas

- **Delays no envío masivo**: Prevén sobrecarga do servidor
- **Limitación de consultas**: Queries optimizadas
- **CSS único**: Cárgase só unha vez por páxina
- **Logging eficiente**: Mantén só os últimos 100 rexistros

### Recomendacións para sitios con alto tráfico

- Usa un servizo de email transaccional (SendGrid, Mailgun, etc.)
- Considera a implementación de colas para envíos masivos grandes
- Monitoriza os logs de envío regularmente

## 🐛 Resolución de Problemas

### Problemas comúns

**O shortcode non aparece:**
- Verifica que o plugin estea activado
- Comproba que o ID da lista sea correcto
- Asegúrate de que a lista existe e está publicada

**Os emails non se envían:**
- Verifica a configuración de email de WordPress
- Comproba os logs do servidor
- Considera usar un plugin de SMTP

**Erro de permisos:**
- Só usuarios administradores poden enviar emails masivos
- Verifica os roles e capacidades do usuario

### Logs e debugging

Os logs de envío almacénanse na base de datos e poden consultarse desde o código:

```php
$logs = get_option( 'ml_email_logs', array() );
```

## 📝 Changelog

### Versión 1.0.2 - Namespaces e Modernización Completa
- ✅ **Implementación de namespaces** `ML_Mailing_Lists` en todas as clases
- ✅ **Arquitectura moderna** seguindo PSR-4 con namespace
- ✅ **Actualización de clases** a nomes sen prefixo (Security, Shortcode, etc.)
- ✅ **Referencias actualizadas** en todo o código para usar namespaces
- ✅ **Documentación actualizada** con exemplos de namespace
- ✅ **Mellor organizacón** evitando conflitos de nomes
- ✅ **Compatibilidade mantida** con todas as funcionalidades

### Versión 1.0.1 - Refactorización Modular
- ✅ **Arquitectura modular completa** con clases separadas
- ✅ **Patrón Singleton** implementado en todas as clases principais
- ✅ **Comentarios de código en inglés** para desenvolvedores
- ✅ **Separación de responsabilidades** clara e modular
- ✅ **Classe ML_Core** como cargador principal do plugin
- ✅ **Classe ML_Security** para toda a xestión de seguridade
- ✅ **Classe ML_Shortcode** para formularios de subscrición
- ✅ **Classe ML_Admin** para interface de administración
- ✅ **Classe ML_Email_Sender** para xestión de emails
- ✅ **Classe ML_Export** para exportación de datos
- ✅ **Funcións auxiliares** organizadas en functions.php
- ✅ Funcionalidade de envío masivo engadida
- ✅ Sistema de exportación implementado
- ✅ Tradución completa ao galego
- ✅ Variables de personalización: `{{nome}}`, `{{apelido}}`, `{{correo}}`
- ✅ Melloras de seguridade implementadas
- ✅ Sistema anti-spam con honeypot e rate limiting
- ✅ Vista previa de emails antes do envío
- ✅ Logging de actividade de envíos
- ✅ **Documentación actualizada** con nova estrutura

### Versión 1.0.0
- ✅ Funcionalidade básica de subscrición
- ✅ Shortcode para formularios
- ✅ Integración con Pods

## 👨‍💻 Desenvolvemento

### Arquitectura moderna con namespaces

O plugin está deseñado cunha **arquitectura moderna con namespaces** que facilita:

- **Mantemento**: Cada funcionalidade en súa propia clase con namespace
- **Testing**: Classes independentes fáciles de probar
- **Extensibilidade**: Novos módulos pódense engadir facilmente
- **Compatibilidade**: Namespaces evitan conflitos con outros plugins
- **Legibilidade**: Código organizado e ben documentado
- **Namespaces**: Evita conflitos de nomes entre plugins
- **Autoloading**: Estrutura preparada para autoloaders PSR-4

### Estándares implementados

- ✅ **Namespaces PSR-4** para organización do código
- ✅ **Patrón Singleton** para clases principais
- ✅ **Hooks e filtros nativos** de WordPress
- ✅ **Sanitización e validación** estricta
- ✅ **Nonces de seguridade** en todos os formularios
- ✅ **Estándares de codificación** de WordPress
- ✅ **Comentarios en inglés** para desenvolvedores
- ✅ **Separación de responsabilidades** clara
- ✅ **Prevención de execución directa** con ABSPATH
- ✅ **Compatibilidade con PHP 7.4+**

### Estrutura de namespaces

```php
namespace ML_Mailing_Lists;

// Clases principais
Core::class           // \ML_Mailing_Lists\Core
Security::class       // \ML_Mailing_Lists\Security
Shortcode::class      // \ML_Mailing_Lists\Shortcode
Admin::class          // \ML_Mailing_Lists\Admin
Email_Sender::class   // \ML_Mailing_Lists\Email_Sender
Export::class         // \ML_Mailing_Lists\Export
```

### Clases principais e os seus métodos

#### ML_Mailing_Lists\Core
```php
// Inicialización do plugin
\ML_Mailing_Lists\Core::get_instance();

// Métodos principais
->init_plugin()          // Inicializa todos os módulos
->load_dependencies()    // Carga arquivos de clases
->load_textdomain()      // Carga traduccións
```

#### ML_Mailing_Lists\Security
```php
// Métodos de seguridade (estáticos)
use ML_Mailing_Lists\Security;

Security::verify_nonce($nonce, $action);
Security::create_nonce($action);
Security::check_rate_limit($ip);
Security::validate_subscription_data($data);
Security::get_user_ip();
```

#### ML_Mailing_Lists\Shortcode
```php
// Xestión de shortcodes
use ML_Mailing_Lists\Shortcode;

$shortcode = Shortcode::get_instance();
$shortcode->subscription_form_shortcode($atts);
```

### Estrutura de datos

#### Custom Post Type: `ml_suscriptor`
```php
// Metadatos almacenados
'nome'               => string    // Nome do subscritor
'apelido'            => string    // Apelido do subscritor
'correo'             => string    // Email do subscritor
'data_subscripcion'  => datetime  // Data de subscrición
'ml_ip_address'      => string    // IP de rexistro
```

#### Taxonomía: `ml_lista`
```php
// Termos que representan as listas de correo
'name'        => string  // Nome da lista
'description' => string  // Descrición da lista
'count'       => int     // Número de subscriptores
```

### Funcións auxiliares globais

```php
// Verificar subscrición existente
ml_subscription_exists($email, $list_id);

// Obter datos de subscritor
ml_get_subscriber_by_email($email);

// Obter subscriptores dunha lista
ml_get_list_subscribers($list_id);

// Estatísticas
ml_get_list_stats($list_id);

// Logging
ml_log_activity($action, $details, $user_id);

// Validación
ml_is_valid_email($email);

// Permisos
ml_user_can_manage_lists($user_id);
```

### Para desenvolvedores

O plugin segue as mellores prácticas de WordPress:

- Uso de hooks e filtros nativos
- Sanitización e validación de datos
- Nonces para seguridade
- Estándares de codificación de WordPress
- Arquitectura modular e extensible
- Documentación completa en inglés

### Contribucións

As contribucións son benvidas. Por favor:

1. Fork o repositorio
2. Crea unha rama para a túa feature
3. Segue os estándares de codificación de WordPress
4. Envía un pull request

## 📄 Licenza

GPL2 - Consulta el archivo de licencia para más detalles.

## 👤 Autor

**Carlos Longarela**
- Website: [https://tabernawp.com/](https://tabernawp.com/)

## 🙏 Agradecementos

- Comunidade de WordPress
- Desenvolvedores do Plugin Pods
- Beta testers e usuarios que proporcionaron o feedback

---

*Para soporte técnico ou consultas, contacta a través do sitio web do autor.*

---

### 🔧 Uso avanzado con namespaces

#### Importar clases específicas

```php
// Importar clases específicas para uso local
use ML_Mailing_Lists\Security;
use ML_Mailing_Lists\Shortcode;

// Agora podes usar as clases sen o namespace completo
$nonce = Security::create_nonce('mi_accion');
$shortcode = Shortcode::get_instance();
```

#### Extensión do plugin

```php
// Crear un módulo personalizado que extenda a funcionalidade
namespace ML_Mailing_Lists\Extensions;

use ML_Mailing_Lists\Core;
use ML_Mailing_Lists\Security;

class Mi_Extension {
    public function __construct() {
        // Asegúrate de que o plugin principal está cargado
        if (class_exists('\ML_Mailing_Lists\Core')) {
            $this->init();
        }
    }

    private function init() {
        // Usar as clases do plugin principal
        add_action('ml_subscription_created', array($this, 'on_subscription'));
    }

    public function on_subscription($post_id, $email, $list_id) {
        // A túa lógica personalizada aquí
    }
}
```

#### Hooks e filtros con namespace

```php
// Os hooks seguen funcionando igual, pero as clases usan namespace
add_filter('ml_subscription_email', function($email, $list_id) {
    // Validación adicional usando a clase Security
    return \ML_Mailing_Lists\Security::sanitize_email_input($email);
}, 10, 2);

// Hook para despois de crear unha subscrición
add_action('ml_subscription_created', function($post_id, $email, $list_id) {
    // Obter instancia das clases usando namespace
    $email_sender = \ML_Mailing_Lists\Email_Sender::get_instance();
    // Usar a instancia...
}, 10, 3);
```
