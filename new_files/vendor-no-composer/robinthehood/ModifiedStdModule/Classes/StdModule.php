<?php

namespace RobinTheHood\ModifiedStdModule\Classes;

$rthModifiedStdModuleMessages = [];

/**
 * Class StdModule
 *
 * This class provides a standard module structure.
 *
 * You can find more informations of module developing for modified at
 * https://docs.module-loader.de
 */
class StdModule
{
    /**
     * @var string Current version of the module.
     *      Used by StdModule code.
     */
    public const VERSION = '';



    /**
     * Constants for update status.
     */

     /** @var int */
    public const UPDATE_ERROR = -1;

    /** @var int */
    public const UPDATE_NOTHING = -1;

    /** @var int */
    public const UPDATE_SUCCESS = 1;



    /**
     * Constants for message types.
     */

     /** @var int */
    public const MESSAGE_ERROR = -1;

    /** @var int */
    public const MESSAGE_SUCCESS = 1;



    /**
     * Used by modified code. We can not type hint.
     *
     * @var string $code A unique code of the module.
     */
    public $code;

    /**
     * Used by modified code. We can not type hint.
     *
     * @var string $title The title displayed in the backend of the module
     */
    public $title;

    /**
     * Used by modified code. We can not type hint.
     *
     * @var string $title The description displayed in the backend of the module
     */
    public $description;

    /**
     * Used by modified code. We can not type hint.
     *
     * @var bool $enabled Indicates whether the module is enabled.
     */
    public $enabled;

    /**
     * @var bool $installed Indicates whether the module is installed or not.
     */
    private bool $installed;

    /**
     * Used by modified code. We can not type hint.
     *
     * @var int $sort_order The position in the backend at which the module should be displayed in the backend in a list
     *      with other modules.
     */
    public $sort_order;

    /**
     * Used by StdModule code. We can not type hint.
     *
     * @var string $modulePrefix The prefix of the module. E.g. `MODULE_MY_FIRST_MODULE`
     */
    public $modulePrefix;

    /**
     * Used by modified code. We can not type hint.
     *
     * @var string[] $keys An array of configuration key names of the module in uppercase. E.g.
     *      `MODULE_MC_MY_FIRST_MODULE_STATUS`, `MODULE_MC_MY_FIRST_MODULE_SIZE`
     */
    public $keys = [];

    /**
     * @var string $tempVersion A temporary version for module updates.
     */
    private string $tempVersion;

    /**
     * An array containing information about module actions.
     *
     * @var array[] $actions
     * @example ...
     * [
     *     [
     *         'functionName' => 'update',
     *         'buttonName' => 'Update'
     *     ],
     *     // Additional entries can be added similarly
     * ]
     */
    private array $actions = [];

    /**
     * Checks if a module is enabled.
     *
     * @param string $module The name of the module. E.g. `MODULE_MC_MY_FIRST_MODULE`
     * @return bool True if the module is enabled, false otherwise.
     */
    public static function isEnabled(string $module): bool
    {
        $statusConstant = $module . '_STATUS';

        if (defined($statusConstant) && 'true' === constant($statusConstant)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a module is disabled.
     *
     * @param string $module The name of the module. E.g. `MODULE_MC_MY_FIRST_MODULE`
     * @return bool True if the module is disabled, false otherwise.
     */
    public static function isDisabled(string $module): bool
    {
        $isDisabled = !self::isEnabled($module);

        return $isDisabled;
    }

    /**
     * Constructor for the StdModule class.
     *
     * @param string $modulePrefix The prefix of the module. E.g. `MODULE_MC_MY_FIRST_MODULE`
     * @param string $code The code of the module.
     */
    public function __construct(string $modulePrefix = '', string $code = '')
    {
        $class = get_class($this);

        if ($modulePrefix) {
            $this->modulePrefix = $modulePrefix;
        } else {
            $this->modulePrefix = 'MODULE_' . strtoupper($class);
        }

        if ($code) {
            $this->code = $code;
        } else {
            $this->code = $class;
        }

        $this->title = $this->getTitle();
        $this->description = $this->getDescription();
        $this->sort_order = $this->getSortOrder();
        $this->enabled = $this->getEnabled();

        $this->addKey('STATUS');
    }

    /**
     * Initializes the module.
     *
     * @deprecated Deprecated. Use parent::__construct() instead.
     *
     * @param string $modulePrefix The prefix of the module. E.g. `MODULE_MC_MY_FIRST_MODULE`
     * @param string $code The code of the module.
     */
    protected function init(string $modulePrefix, string $code = ''): void
    {
        self::__construct($modulePrefix, $code);

        /** E_USER_DEPRECATED does not work */
        trigger_error('Using the init method is deprecated. Use parent::__construct instead.', E_USER_NOTICE);
    }

    /**
     * Adds a splash message above the module list in the backend.
     *
     * @param string $message The message.
     * @param int $messageType The message type (MESSAGE_ERROR or MESSAGE_SUCCESS).
     */
    protected function addMessage(string $message, int $messageType = self::MESSAGE_ERROR): void
    {
        global $rthModifiedStdModuleMessages;

        $hash = md5($message);

        if (!$rthModifiedStdModuleMessages) {
            echo '<br>';
        }

        if ($messageType == self::MESSAGE_ERROR) {
            $class = 'error_message';
        } elseif ($messageType == self::MESSAGE_SUCCESS) {
            $class = 'success_message';
        } else {
            $class = 'error_message';
        }

        if (!$rthModifiedStdModuleMessages[$hash]) {
            echo '<div class="' . $class . '">' . $message . '</div>';
            $rthModifiedStdModuleMessages[$hash][$hash] = $message;
        }
    }

    /**
     * Adds a configuration key to the module.
     *
     * @param string $key The key. E.g. `SIZE`, `WEIGHT`, etc.
     */
    protected function addKey(string $key): void
    {
        $fullKeyName = $this->getModulePrefix() . '_' . $key;
        if (in_array($fullKeyName, $this->keys())) {
            return;
        }

        $this->keys[] = $fullKeyName;
    }

    /**
     * Gets the title of the module, including the version if available.
     *
     * @return string The module title, optionally appended with the version in the format " (vX.X.X)".
     */
    private function getTitle(): string
    {
        $version = $this->getVersion();
        $title = $this->getConfig('TITLE');
        if ($version) {
            return $title . ' (v' . $version . ')';
        }
        return $title;
    }

    /**
     * Gets the description from the language file of the module.
     *
     * @return string The module description from the language file of the current language.
     */
    private function getDescription(): string
    {
        return $this->getConfig('LONG_DESCRIPTION');
    }

    /**
     * Gets the sort order of the module.
     *
     * @return int The sort order value for displaying the module in the backend list.
     */
    private function getSortOrder(): int
    {
        $sortOrder = $this->getConfig('SORT_ORDER', 0);

        return (int) $sortOrder;
    }

    /**
     * Checks if the module is enabled.
     *
     * @return bool True if the module is enabled, false otherwise.
     */
    public function getEnabled(): bool
    {
        $status = strtolower($this->getConfig('STATUS'));
        if ($status == 'true') {
            return true;
        }
        return false;
    }

    /**
     * Gets the prefix of the module.
     *
     * @return string The prefix of the module. E.g., `MODULE_MC_MY_FIRST_MODULE`
     */
    public function getModulePrefix(): string
    {
        return $this->modulePrefix;
    }

    /**
     * Gets the configuration value for a given configuration key.
     *
     * @param string $name    The key of the configuration.
     * @param mixed  $default The default value to return if the configuration key is not defined.
     *
     * @return string The configuration value for the specified key, or the default value if the key is not defined.
     */
    protected function getConfig(string $name, $default = false): string
    {
        $constantName = $this->getModulePrefix() . '_' . $name;
        $configurationValue = defined($constantName) ? constant($constantName) : $default;

        return $configurationValue;
    }

    /**
     * Gets the version of the module.
     *
     * If the temporary version (`tempVersion`) is not set, it retrieves the version
     * from the configuration and saves it in `tempVersion`. This is done because changes
     * to the database constant `VERSION` only take effect after a reload. The `setVersion`
     * method also always saves a new value in `tempVersion`.
     *
     * @return string The version of the module.
     */
    protected function getVersion(): string
    {
        if (!$this->tempVersion) {
            $this->tempVersion = $this->getConfig('VERSION');
        }
        return $this->tempVersion;
    }

    /**
     * Sets the version of the module.
     *
     * It updates the temporary version (`tempVersion`), removes the existing configuration
     * for 'VERSION', and adds a new configuration entry for 'VERSION' with the provided version.
     *
     * @param string $version The new version to set for the module.
     */
    protected function setVersion(string $version): void
    {
        $this->tempVersion = $version;
        $this->removeConfiguration('VERSION', $version);
        $this->addConfiguration('VERSION', $version, 6, 1);
    }

    /**
     * Used by modified code. We can not type hint.
     */
    public function process($file)
    {
    }

    /**
     * Used by modified code. We can not type hint.
     */
    public function display()
    {
    }

    protected function displaySaveButton(): array
    {
        $buttonLink = xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code);

        $html = '
            <br><div align="center">'
            . xtc_button(BUTTON_SAVE)
            . xtc_button_link(BUTTON_CANCEL, $buttonLink)
            . '</div>';

        return [
            'text' => $html
        ];
    }

    /**
     * Used by modified code. We can not type hint.
     */
    public function check()
    {
        if (!isset($this->installed)) {
            $key = $this->getModulePrefix() . '_STATUS';

            $query = xtc_db_query(
                "SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '$key'"
            );
            $this->installed = xtc_db_num_rows($query);
        }

        return $this->installed;
    }

    /**
     * Used by modified code. We can not type hint.
     */
    public function install()
    {
        $this->addConfigurationSelect('STATUS', 'true', 6, 1);

        $installedVersion = $this->getVersion();

        if (static::VERSION && !$installedVersion) {
            $this->setVersion(static::VERSION);
        }
    }

    /**
     * Used by modified code. We can not type hint.
     */
    public function remove()
    {
        $this->removeConfiguration('STATUS');

        if ($this->getVersion()) {
            $this->removeConfiguration('VERSION');
        }
    }

    /**
     * Used by modified code. We can not type hint.
     */
    public function keys()
    {
        return $this->keys;
    }

    protected function addConfigurationSelect(
        string $key,
        string $value,
        int $groupId,
        int $sortOrder
    ): void {
        $this->addConfiguration($key, $value, $groupId, $sortOrder, 'select');
    }

    protected function addConfigurationTextArea(
        string $key,
        string $value,
        int $groupId,
        int $sortOrder
    ): void {
        $this->addConfiguration($key, $value, $groupId, $sortOrder, 'textArea');
    }

    protected function addConfigurationOrderStatus(
        string $key,
        string $value,
        int $groupId,
        int $sortOrder
    ): void {
        $this->addConfiguration($key, $value, $groupId, $sortOrder, 'orderStatus', 'xtc_get_order_status_name');
    }

    protected function addConfigurationDropDown(
        string $key,
        string $value,
        int $groupId,
        int $sortOrder,
        array $values
    ): void {
        $arrayAsString = "['" . implode("','", $values) .  "']";
        $setFunction = 'xtc_cfg_select_option(' . $arrayAsString . ',';
        $this->addConfiguration($key, $value, $groupId, $sortOrder, $setFunction);
    }

    protected function addConfigurationDropDownByStaticFunction(
        string $key,
        string $value,
        int $groupId,
        int $sortOrder,
        string $staticCallFunctionName
    ): void {
        $setFunction = 'xtc_cfg_select_option(' . get_class($this) . '::' . $staticCallFunctionName . '(),';
        $this->addConfiguration($key, $value, $groupId, $sortOrder, $setFunction);
    }

    protected function addConfiguration(
        string $key,
        string $value,
        int $groupId,
        int $sortOrder,
        string $setFunction = '',
        string $useFunction = ''
    ): void {
        $key = $this->getModulePrefix() . '_' . $key;

        if ($setFunction == 'select') {
            $setFunction = "xtc_cfg_select_option(array('true', 'false'),";
        } elseif ($setFunction == 'textArea') {
            $setFunction = "xtc_cfg_textarea(";
        } elseif ($setFunction == 'orderStatus') {
            $setFunction = "xtc_cfg_pull_down_order_statuses(";
        }

        $setFunction = str_replace("'", "\\'", $setFunction);

        xtc_db_query(
            "INSERT INTO `" . TABLE_CONFIGURATION . "`
            (
                `configuration_key`,
                `configuration_value`,
                `configuration_group_id`,
                `sort_order`,
                `set_function`,
                `use_function`,
                `date_added`
            )
                VALUES
            (
                '$key', 
                '$value', 
                '$groupId', 
                '$sortOrder', 
                '$setFunction', 
                '$useFunction', 
                NOW()
            )"
        );
    }

    protected function removeConfigurationAll(): bool
    {
        $module_name = $this->getModulePrefix();
        $remove_configuration = xtc_db_query(
            sprintf(
                /** TRANSLATORS: %1$s: Database table "configuration". %2$s: Value for "configuration_key". */
                'DELETE FROM `%1$s` WHERE `configuration_key` LIKE "%2$s_%"',
                TABLE_CONFIGURATION,
                $module_name
            )
        );

        if (false === $remove_configuration) {
            return false;
        }

        return true;
    }

    protected function removeConfiguration(string $key): bool
    {
        $key              = $this->getModulePrefix() . '_' . $key;
        $remove_key_query = xtc_db_query(
            sprintf(
                /** TRANSLATORS: %1$s: Database table "configuration". %2$s: Value for "configuration_key". */
                'DELETE FROM `%1$s` WHERE `configuration_key` = "%2$s"',
                TABLE_CONFIGURATION,
                $key
            )
        );

        $success = false !== $remove_key_query;

        return $success;
    }

    protected function deleteConfiguration(string $key): bool
    {
        /** E_USER_DEPRECATED does not work */
        trigger_error(
            'Using the deleteConfiguration method is deprecated. Use removeConfiguration instead.', E_USER_NOTICE
        );

        return $this->removeConfiguration($key);
    }

    protected function renameConfiguration(string $oldKey, string $newKey): void
    {
        $oldKey = $this->getModulePrefix() . '_' . $oldKey;
        $newKey = $this->getModulePrefix() . '_' . $newKey;

        xtc_db_query(
            "UPDATE `" . TABLE_CONFIGURATION . "`
            SET `configuration_key` = '$newKey'
            WHERE `configuration_key` = '$oldKey'"
        );
    }

    protected function setAdminAccess(string $key): void
    {
        xtc_db_query("ALTER TABLE `" . TABLE_ADMIN_ACCESS . "` ADD `$key` INT(1) NOT NULL DEFAULT 0");
        xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `$key` = 1 WHERE `customers_id` = 1");
        xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `$key` = 1 WHERE `customers_id` = 'groups'");

        /** Set access for admin who doesn't have an ID of 1 */
        if (isset($_SESSION['customer_id']) && '1' !== $_SESSION['customer_id']) {
            $accessExistsQuery = xtc_db_query(
                "SELECT * FROM " . TABLE_ADMIN_ACCESS . ' WHERE `customers_id` = ' . $_SESSION['customer_id']
            );

            if (xtc_db_num_rows($accessExistsQuery) >= 1) {
                xtc_db_query(
                    "UPDATE `" . TABLE_ADMIN_ACCESS . "`
                    SET `$key` = 1
                    WHERE `customers_id` = " . $_SESSION['customer_id']
                );
            }
        }
    }

    protected function deleteAdminAccess($key): void
    {
        xtc_db_query("ALTER TABLE " . TABLE_ADMIN_ACCESS . " DROP $key");
    }

    /**
     * Checks for module updates. Returns whether an update is available.
     *
     * @param bool $showUpdateButton Whether to show the Update button.
     *
     * @return bool
     */
    protected function checkForUpdate(bool $showUpdateButton = false): bool
    {
        if (!$this->isOnAdminModulesPage()) {
            return false;
        }

        /** Abort if the user is not an admin */
        if (!$this->isAdmin()) {
            return false;
        }

        if (!$this->enabled) {
            return false; // do not check for update
        }

        if (!static::VERSION) {
            return false; // do not check for update
        }

        $this->invokeAction();

        $action = $_GET['action'] ?? '';
        if ($action) {
            return false; // do not check for update
        }

        $moduleAction = $_GET['moduleaction'] ?? '';
        if ($moduleAction) {
            return false; // do not check for update
        }

        if (-1 !== version_compare($this->getVersion(), static::VERSION)) {
            return false; // module is up-to-date
        }

        if ($this->getVersion()) {
            $from = ' von ' . $this->getVersion();
        }

        // TODO: extract to own private method
        if (isset($_SERVER['SCRIPT_NAME'], $_GET['set'])) {
            $moduleLink = xtc_href_link(
                pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_BASENAME),
                http_build_query(
                    [
                        'set' => $_GET['set'],
                        'module' => $this->code
                    ]
                ),
                'SSL'
            );

            $moduleName = '<a href="' . $moduleLink . '">' . $this->getConfig('TITLE') . '</a>';
        } else {
            $moduleName = $this->getConfig('TITLE');
        }

        $this->addMessage(
            sprintf(
                /** TRANSLATORS: %1$s: Module name. %2$s: Module current version. %3$s: Module new version. */
                '%1$s benötigt ein Update von %2$s auf %3$s - Klicken Sie dafür beim Modul auf Update.',
                $moduleName,
                $from,
                static::VERSION
            )
        );

        if ($showUpdateButton) {
            $this->addAction('update', 'Update');
        }

        return true;
    }

    private function isOnPage(string $targetPage): bool
    {
        $currentPage = $_SERVER['PHP_SELF'];

        if (substr($currentPage, -strlen($targetPage)) === $targetPage) {
            return true;
        }

        return false;
    }

    private function isOnAdminModulesPage(): bool
    {
        if (!defined('RUN_MODE_ADMIN')) {
            return false;
        }

        if (!RUN_MODE_ADMIN) {
            return false;
        }

        if ($this->isOnPage('modules.php')) {
            return true;
        }

        if ($this->isOnPage('module_export.php')) {
            return true;
        }

        return false;
    }

    // TODO: Check whether we can set the visibility of the method to private or protected.
    public function invokeUpdate(): void
    {
        $status = '';
        while ($status != self::UPDATE_NOTHING) {
            $versionBefore = $this->getVersion();
            $status = $this->updateSteps();
            $versionAfter = $this->getVersion();

            if ($versionBefore == $versionAfter) {
                break;
            }
        }

        $this->title = $this->getTitle();

        $this->addMessage(
            'Update von ' . $this->getConfig('TITLE') .
            ' auf Version ' . $this->getVersion() . ' erfolgreich.',
            self::MESSAGE_SUCCESS
        );
    }

    /**
     * Warning: We can not add type hint return int withod breaking changes, becaus most modules overwrite this
     * method without :int.
     *
     * Can be overridden by the module class to define tasks for updating the module. For more information,
     * refer to the documentation: https://module-loader.de/docs/module_update_with_std_module.php
     *
     * @return int The update status. Possible values are `UPDATE_NOTHING`, `UPDATE_SUCCESS`, or `UPDATE_ERROR`.
     */
    protected function updateSteps()
    {
        return self::UPDATE_NOTHING;
    }

    /**
     * Adds a separate button (next to Install, Uninstall, etc.) on the right side of the module overview in the
     * backend.
     *
     * In order for the button to be able to call the function, a method with the prefix `invoke` must be added to the
     * module class. E.g If the `functionName` is `calcSize` the Module needs a method named `invokeCalcSize()`.
     *
     * @param string $functionName The name of the function to invoke.
     * @param string $buttonName The name displayed on the action button.
     */
    protected function addAction(string $functionName, string $buttonName = ''): void
    {
        if (!$this->enabled) {
            return;
        }

        $buttonName = $buttonName ?? $functionName;

        $this->actions[] = [
            'functionName' => $functionName,
            'buttonName' => $buttonName
        ];

        $buttons = '';
        foreach ($this->actions as $action) {
            $buttons .= $this->renderButton($action['functionName'], $action['buttonName']);
        }

        $this->description = $buttons . $this->getDescription();

        $this->invokeAction();
    }

    private function invokeAction(): void
    {
        $module = $_GET['module'] ?? '';
        if ($module != $this->code) {
            return;
        }

        $functionName = $_GET['moduleaction'] ?? '';
        $functionName = 'invoke' . ucfirst($functionName);

        if (!method_exists($this, $functionName)) {
            return;
        }

        $this->$functionName();
    }

    private function renderButton(string $functionName, string $buttonName): string
    {
        if (!isset($_SERVER['SCRIPT_NAME'], $_GET['set'])) {
            return '';
        }

        $url = xtc_href_link(
            pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_BASENAME),
            http_build_query(
                [
                    'set' => $_GET['set'],
                    'module' => $this->code,
                    'moduleaction' => $functionName
                ]
            )
        );

        return '
            <a class="button btnbox" style="text-align:center;" onclick="this.blur();" href="' . $url . '">'
            . $buttonName
            . '</a>
        ';
    }

     /**
     * Checks whether the user is an admin.
     *
     * @return bool True if the user is an admin, false otherwise.
     */
    private function isAdmin(): bool
    {
        $customerStatusId = $_SESSION['customers_status']['customers_status_id'] ?? '';
        if ($customerStatusId !== '0') {
            return false;
        }
        return true;
    }
}
