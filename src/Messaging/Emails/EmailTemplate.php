<?php
namespace Messaging\Emails;
/**
 * @package EmailTemplate 
 * @author Amadi Ifeanyi <amadiify.com>
 */
class EmailTemplate
{
    /**
     * @method EmailTemplate LoadBusinessTemplate
     * @param string $templateName
     * @param array $data 
     * @param array $config 
     * 
     * This method loads a template from the business directory
     */
    public static function LoadBusinessTemplate(string $templateName, array $data, array $config = [])
    {
        return self::getTemplateData('Business', $templateName, $data, $config);
    }

    /**
     * @method EmailTemplate LoadTransactionalTemplate
     * @param string $templateName
     * @param array $data 
     * 
     * This method loads a template from the transactional directory
     */
    public static function LoadTransactionalTemplate(string $templateName, array $data)
    {
        return self::getTemplateData('Transactional', $templateName, $data, $config);
    }

    /**
     * @method EmailTemplate CleanUpName
     * @param string $name
     * @return void
     */
    private static function CleanUpName(string $name)
    {
        $name = preg_replace('/[\s]+/', '-', trim($name));

        // return name
        return $name . '.html';
    }

    /**
     * @method EmailTemplate getTemplateData
     * @param string $category
     * @param string $templateName
     * @param array $data
     * @param array $config
     * @return mixed
     */
    private static function getTemplateData(string $category, string $templateName, array $data, array $config = [])
    {
        // has entities
        if (isset($config['entities'])) :

            $configData = (array) $config['entities'];

            // all good ?
            if (count($configData) > 0) :

                // rule applies
                $filter = filter($data, $configData);

                // failed
                if (!$filter->isOk()) return self::EntitiesNotValid($templateName, $templateFile);

                // update data
                $data = $filter->data();

            endif;

        endif;

        // load template
        $templateFile = __DIR__ . '/Templates/'.$category.'/' . self::CleanUpName($templateName);

        // template file does not exists
        if (!file_exists($templateFile)) return self::TemplateDoesNotExists($templateName, $templateFile);

        // load template
        $templateData = file_get_contents($templateFile);

        // run loop
        foreach ($data as $key => $value) :

            // replace from template data
            $templateData = str_replace(strtoupper('{'.$key.'}'), $value, $templateData);
            $templateData = str_replace(strtolower('{'.$key.'}'), $value, $templateData);

        endforeach;

        // add date
        $templateData = preg_replace('/[\{]+(@date)+[\}]/', date('F jS Y'), $templateData);

        // return string
        return $templateData;
    }

    /**
     * @method EmailTemplate TemplateDoesNotExists
     * @param string $templateName
     * @param string $templateFile
     * @return void
     */
    private static function TemplateDoesNotExists(string $templateName, string $templateFile) : void
    {
        // logger path
        $path = __DIR__ . '/error.log';

        // open file
        $file = fopen($path, 'a+');
        fwrite($file, "\n".'['.date('d-m-Y g:i:s a').'] Template "'.$templateName.'" was not found. See full path "'.$templateFile.'"');
        fclose($file);
    }

    /**
     * @method EmailTemplate EntitiesNotValid
     * @param string $templateName
     * @param string $templateFile
     * @return void
     */
    private static function EntitiesNotValid(string $templateName, string $templateFile) : void
    {
        // logger path
        $path = __DIR__ . '/error.log';

        // open file
        $file = fopen($path, 'a+');
        fwrite($file, "\n".'['.date('d-m-Y g:i:s a').'] Entities for "'.$templateName.'" did not match. See full path "'.$templateFile.'"');
        fclose($file);
    }
}