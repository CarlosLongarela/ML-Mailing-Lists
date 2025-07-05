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

**Filtros:**
```php
// Personalizar datos antes de gardar a subscrición
add_filter( 'ml_subscription_name', 'mi_filtro_nome', 10, 2 );
add_filter( 'ml_subscription_surname', 'mi_filtro_apelido', 10, 2 );
add_filter( 'ml_subscription_email', 'mi_filtro_email', 10, 2 );
```

**Accións:**
```php
// Executar código despois de crear unha subscrición
add_action( 'ml_subscription_created', 'mi_funcion_post_subscripcion', 10, 3 );
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

## 📁 Estrutura de Arquivos

```
ml-mailing-lists/
├── ml-mailing-lists.php     # Arquivo principal do plugin
├── README.md                # Este arquivo
└── SECURITY_IMPROVEMENTS.md # Documentación de melloras de seguridade
```

## 🌐 Idiomas

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

1. Ve a **ML Mailing Lists > ML Lista**
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

### Versión 1.0.1
- ✅ Funcionalidade de envío masivo engadida
- ✅ Sistema de exportación implementado
- ✅ Tradución completa ao galego
- ✅ Variables de personalización: `{{nome}}`, `{{apelido}}`, `{{correo}}`
- ✅ Melloras de seguridade implementadas
- ✅ Sistema anti-spam con honeypot e rate limiting
- ✅ Vista previa de emails antes do envío
- ✅ Logging de actividade de envíos

### Versión 1.0.0
- ✅ Funcionalidade básica de subscrición
- ✅ Shortcode para formularios
- ✅ Integración con Pods

## 👨‍💻 Desenvolvemento

### Para desenvolvedores

O plugin segue as mellores prácticas de WordPress:

- Uso de hooks e filtros nativos
- Sanitización e validación de datos
- Nonces para seguridade
- Estándares de codificación de WordPress
- Arquitectura modular e extensible

### Contribucións

As contribucións son benvidas. Por favor:

1. Fork o repositorio
2. Crea unha rama para a túa feature
3. Segue os estándares de codificación de WordPress
4. Envía un pull request

## 📄 Licencia

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
