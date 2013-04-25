<?php

class GlaucomaVirtualClinicModule extends BaseEventTypeModule {

    public function init() {
        // this method is called when the module is being created
        // you may place code here to customize the module or the application
        // import the module-level models and components
        $this->setImport(array(
            'GlaucomaVirtualClinic.models.*',
            'GlaucomaVirtualClinic.components.*',
        ));
        
        parent::init();
    }

    /**
     * Format of the columns is an array of coumn names (as the index)
     * against an array of contruction data for the clinic row. The idea is
     * that each column contains at least one piece of information, possibly
     * more (like for readings that have two values, one for L/E and R/E).
     * 
     * Each column is defined by obtaining data from one event type (although
     * different columns can have different event types); one class name
     * for that event type; and a field, which is either a string representing
     * a property on the object, or an array defining a nested object
     * hierarchy (for one value of a property), or an array of arrays
     * containing nested object properties, used when several properties
     * are required for a column.
     * 
     * For example, to obtain a property directly from a class object, use
     * the format:
     * 
     * 'History' => array(
     *       'event_type' => 'OphCiExamination',
     *       'class_name' => 'Element_OphCiExamination_History',
     *       'field' => 'description')
     * 
     * For a nested property, use the format:
     * 
     * 'IOP' => array(
     *       'event_type' => 'OphCiExamination',
     *       'class_name' => 'Element_OphCiExamination_IntraocularPressure',
     *       'field' => array('left_reading', 'value'))
     * 
     * Taking this further we can create an array of properties for two
     * readings for the IOP element:
     * 
     * 'IOP' => array(
     *       'event_type' => 'OphCiExamination',
     *       'class_name' => 'Element_OphCiExamination_IntraocularPressure',
     *       'field' => array(array('left_reading', 'value'),
     *                        array('right_reading', 'value'))
     * 
     * @var type 
     */
    public $columns = array('IOP' => array(
            'event_type' => 'OphCiExamination',
            'class_name' => 'Element_OphCiExamination_IntraocularPressure',
            'field' => array(array('left_reading', 'value'), array('right_reading', 'value'))),
        'CCT' => array(
            'event_type' => 'OphCiExamination',
            'class_name' => 'Element_OphCiExamination_CentralCornealThickness',
            'field' => array(array('left_cct'), array('right_cct'))),
        'Comments' => array(
            'event_type' => 'OphCiExamination',
            'class_name' => 'Element_OphCiExamination_GlaucomaManagement',
            'field' => 'comments'),
        'Medications' => array(
            'event_type' => 'OphCiExamination',
            'class_name' => 'Element_OphCiExamination_GlaucomaManagement',
            'field' => array(array('med_1_right', 'shortname'), 
                array('med_2_right', 'shortname'), 
                array('med_3_right', 'shortname'),
                array('med_1_left', 'shortname'),
                array('med_2_left', 'shortname'),
                array('med_3_left', 'shortname'))));

    /**
     * Enables custom formatting of table data.
     * 
     * @param string $columnName the column name being formatted.
     * 
     * @param mixed $data data passed in representing the column's value.
     * 
     * @return string formatted text, if the specified column had custom
     * formatting; null otherwise.
     */
    public static function formatData($columnName, $data) {
        $text = null;
        if (($columnName == 'IOP' || $columnName == 'CCT') && $data) {
            if ($data[0] && $data[1]) {
                $text = "RE: " . $data[1] . "<br>" . "LE: " . $data[0];
            }
        }
        if ($columnName == 'Medications' && $data) {
            for ($i=0; $i < 3; $i++) {
                if ($data[$i]) {
                    $text = $text . substr($data[$i], 0, 3) . "/";
                }
            }
            $FU = $text[strlen($text)-1];
            if ($text[strlen($text)-1] == "/") {
                $text = substr($text, 0, strlen($text)-1);
            }
            if ($data[0] || $data[1] || $data[2]) {
                $text = $text . "<br/>";
            }
            for ($i=3; $i < 6; $i++) {
                if ($data[$i]) {
                    $text = $text . substr($data[$i], 0, 3) . "/";
                }
            }
            if ($text[count($text)-1] == "/") {
                $text = substr($text, 0, count($text));
            }
            if ($text[strlen($text)-1] == "/") {
                $text = substr($text, 0, strlen($text)-1);
            }
        }
        return $text;
    }

    public function beforeControllerAction($controller, $action) {
        if (parent::beforeControllerAction($controller, $action)) {
            // this method is called before any module controller action is performed
            // you may place customized code here
            return true;
        }
        else
            return false;
    }

}
