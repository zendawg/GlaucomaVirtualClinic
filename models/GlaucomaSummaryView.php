<?php

/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
?>
<?php

/**
 * Class to help with the summary view which caters for IOP graphs and
 * stereo image disc files.
 * 
 * Future releases will also include HVF images.
 */
class GlaucomaSummaryView {
    /** How dates are formatted on the graph. */

    const DATE_FORMAT = 'd-m-y';
    /** Width of the date in pixels before displaying the next date. */
    const X_AXIS_DATE_WIDTH = 30;
    /** IOP multiplier for displaying the IOP on the y-axis. */
    const Y_AXIS_IOP_MULTIPLIER = 6.6;

    /** Lits of IOPs values from examinations for the left eye. */
    private $iops_left = array();

    /** Lits of IOPs values from examinations for the right eye. */
    private $iops_right = array();

    /** EyeDraw string for the right eye, built from the right IOP values. */
    private $iopsEyeRight = "";

    /** EyeDraw string for the right eye, built from the left IOP values. */
    private $iopsEyeLeft = "";

    /** EyeDraw string for displaying diagnoses; each diagnosis will be for the
     * left and right eye, together. */
    private $diagnosesLeft = array();

    /** EyeDraw string for displaying diagnoses; each diagnosis will be for the
     * left and right eye, together. */
    private $diagnosesRight = array();

    /** EyeDraw string for displaying medications; each diagnosis will be for the
     * left and right eye, together. */
    private $medicationsLeft = array();

    /** EyeDraw string for displaying medications; each diagnosis will be for the
     * left and right eye, together. */
    private $medicationsRight = array();

    /** String for the EyeDraw graph for the examinations dates. */
    private $eyegraphDates = "";

    /**
     * Constructot; initialise this class and get all IOP values.
     * 
     * @param type $event the event object that is currently linked to the
     * view that fired this constructor.
     */
    function __construct($hos_num) {
        $this->init($hos_num);
    }

    /**
     * Gets the EyeDraw graph IOP string for the right eye.
     * 
     * @return string data of the IOPs for the right eye in EyeDraw graph
     * format.
     */
    function getIOPsEyeRight() {
        return $this->iopsEyeRight;
    }

    /**
     * Gets the EyeDraw graph IOP string for the left eye.
     * 
     * @return string data of the IOPs for the left eye in EyeDraw graph
     * format.
     */
    function getIOPsEyeLeft() {
        return $this->iopsEyeLeft;
    }

    /**
     * Get the graph dates for the EyeDraw graph package.
     * 
     * @return string data for the dates on which examinations occurred.
     */
    function getEyeGraphDates() {
        return $this->eyegraphDates;
    }

    /**
     * Gets all events associated with the episode ID for this event.
     * 
     * @param event $event an event object from the patient's current episode
     * view.
     * 
     * @return array the events, if there were any; the empty list otherwise.
     */
    function getEvents($id) {
        $events = array();
        $patient = Patient::model()->find('hos_num=:hos_num', array(':hos_num' => $id));
        $episodes = $patient->episodes;
        if ($episodes) {
            foreach ($episodes as $episode) {
                foreach ($episode->events as $e) {
                    $events = array_merge($events, array($e));
                }
            }
        }
        return $events;
    }

    /**
     * Get all IOPs associated with the specified event.
     * 
     * @param int $event_id the event ID to search for associated IOPs.
     * 
     * @return array a list of associated IOPs for the given event ID; the empty
     * list otherwise.
     */
    function getIOP($event_id) {
        $iop_criteria = new CDbCriteria;
        $iop_criteria->compare('event_id', $event_id);
        return Element_OphCiExamination_IntraocularPressure::model()->find($iop_criteria);
    }

    /**
     * Get all IOPs associated with the specified event.
     * 
     * @param int $event_id the event ID to search for associated IOPs.
     * 
     * @return array a list of associated IOPs for the given event ID; the empty
     * list otherwise.
     */
    function getDiagnosis($event_id) {
        $iop_criteria = new CDbCriteria;
        $iop_criteria->compare('event_id', $event_id);
        $iop_criteria->distinct = true;
        return ElementGlaucomaDiagnosis::model()->find($iop_criteria);
    }

    /**
     * Get all IOPs associated with the specified event.
     * 
     * @param int $event_id the event ID to search for associated IOPs.
     * 
     * @return array a list of associated IOPs for the given event ID; the empty
     * list otherwise.
     */
    function getMedications($event_id) {
        $iop_criteria = new CDbCriteria;
        $iop_criteria->compare('event_id', $event_id);
        $iop_criteria->distinct = true;
        return ElementPrescribedMedication::model()->findAll($iop_criteria);
    }

    /**
     * Get IOPs for the left eye, if there are any.
     * 
     * @return an array of IOPs for the left eye, if there are any; otherwise,
     * the empty list is returned.
     */
    function getIOPsLeft() {
        return $this->iops_left;
    }

    /**
     * Get IOPs for the right eye, if there are any.
     * 
     * @return an array of IOPs for the right eye, if there are any; otherwise,
     * the empty list is returned.
     */
    function getIOPsRight() {
        return $this->iops_right;
    }

    /**
     * 
     * @return type
     */
    function getDiagnosesLeft() {
        return $this->diagnosesLeft;
    }

    /**
     * 
     * @return type
     */
    function getDiagnosesRight() {
        return $this->diagnosesRight;
    }

    /**
     * 
     * @return type
     */
    function getMedicationsLeft($hos_num) {

        $medicationOptions = ElementPrescribedMedication::model()->getMedications();
        $events = $this->getEvents($hos_num);

        // keep track of the medications - if the med changes, re-start the
        // count of the medication:
        $medLeft = null;
        $medRight = null;
        // the length keeps track of how long the medication lasts for:
        $medLeftLen = 50;
        $medRighten = 50;

        $e = array();
        foreach ($events as $event) {
            array_push($e, $event->id);
        }

        $criteria = new CDbCriteria;
        $criteria->select = 'medication_1_left';
        $criteria->order = 'event_id asc';
        $criteria->compare('event_id', $e, false, 'or');
        $meds = ElementPrescribedMedication::model()->findAll($criteria);
        $xaxis = 0;
        $tmpMeds = array();
        foreach ($meds as $med) {
            if ($med->medication_1_left) {
                $currentMed = $medicationOptions[$med->medication_1_left];
                array_push($tmpMeds, $currentMed);
            }
            array_push($tmpMeds, null);
        }
        $lastMed = null;
        $q = 0;
        foreach ($tmpMeds as $index => $tmpMed) {
            if ($lastMed != null && $lastMed != $tmpMed) {
                array_push($this->medicationsLeft, "[" . (($q) * 35) . ", 120, "
                        . $medLeftLen . ", "
                        . "\"" . $lastMed . "\"]");
                $q = $index;
            }
            if ($tmpMed != null) {
                $medLeft = $tmpMed;
                $lastMed = $tmpMed;
            } else {
                $lastMed = null;
            }
            if ($index == count($tmpMeds) - 1) {
                array_push($this->medicationsLeft, "[" . (($q) * 35) . ", 120, "
                        . $medLeftLen . ", "
                        . "\"" . $medLeft . "\"]");
            }
        }
        return $this->medicationsLeft;
    }

    /**
     * 
     * @return type
     */
    function getMedicationsRight() {
        return $this->medicationsRight;
    }

    /**
     * Initialise this instance and build up all required information
     * about the IOPs and disc files.
     * 
     * @param event $event the event that was linked to the view of the page
     * that the summary's view was launched; care should be taken to ensure
     * that the event is both valid and contains IOPs (it is up to callers
     * to examine returned data and how to display it).
     */
    function init($hos_num) {
        // Display a bar chart of IOPs - first, for the left eye:

        $events = $this->getEvents($hos_num);
        $this->eyegraphDates = "eyeGraph.axisXArray = [";
        $this->iopsEyeLeft = "";
        $this->iopsEyeRight = "";

        // keep track of the medications - if the med changes, re-start the
        // count of the medication:
//        $medLeft = "";
//        $medRight = "";
        // the length keeps track of how long the medication lasts for:
//        $medLeftLen;
//        $medRighten;
        $dateMod = 0;
        if (count($events) > 0) {
            $this->eyegraphDates = "eyeGraph.axisXArray = [";
            $this->iopsEyeLeft = "";
            $this->iopsEyeRight = "";
            $xaxis = 0;
//            $lastDiagnosisLeft = "";
//            $lastDiagnosisRight = "";
            foreach ($events as $each_event) {
                $event_id = $each_event->id;
//                $diagnosis = $this->getDiagnosis($event_id);
                $iop = $this->getIOP($event_id);
                // TODO for the moment, assume that recording of IOP means
                // that a diagnosis might also be recorded (but not the
                // other way around) for the specified event ID:
                if ($iop) {
                  if ($iop->left_reading) {
                    $foo = $iop->left_reading->value;
                      array_push($this->iops_left, $iop->left_reading->value);
                      $this->iopsEyeLeft .= "[" . $xaxis . ", "
                              . ($iop->left_reading->value * self::Y_AXIS_IOP_MULTIPLIER)
                              . ", \"\", \"" . $iop->left_reading->value . "  \"], ";
                  }
                  if ($iop->right_reading) {
                      array_push($this->iops_right, $iop->right_reading->value);

                      $this->iopsEyeRight .= "[" . $xaxis . ", "
                              . ($iop->right_reading->value * self::Y_AXIS_IOP_MULTIPLIER)
                              . ", \"\", \"" . $iop->right_reading->value . "  \"], ";
                  }
                  $date = date_create($iop->created_date);
                  if ($dateMod++ % 3 == 0) {
                      $newDate = "[" . $xaxis . ", '"
                              . $date->format(self::DATE_FORMAT) . "'], ";
                  } else {
                      $newDate = "[" . $xaxis . ", ''], ";
                  }
                  $this->eyegraphDates .= $newDate;
  //                if ($diagnosis && ($iop->left_reading || $iop->right_reading)) {
  //                    $diagnosisOptions = ElementGlaucomaDiagnosis::model()->getDiagnoses();
  //                    if ($diagnosis->diagnosis_1_left) {
  //                        $x = $diagnosisOptions[$diagnosis->diagnosis_1_left];
  //                        if ($x != $lastDiagnosisLeft) {
  //                            array_push($this->diagnosesLeft, "[" . $xaxis . ", 220, "
  //                                    . "\"" . $x . "\"]");
  //                        }
  //                        $lastDiagnosisLeft = $x;
  //                    }
  //                    if ($diagnosis->diagnosis_1_right) {
  //                        $x = $diagnosisOptions[$diagnosis->diagnosis_1_right];
  //                        if ($x != $lastDiagnosisRight) {
  //                            array_push($this->diagnosesRight, "[" . $xaxis . ", 220, "
  //                                    . "\"" . $x . "\"]");
  //                        }
  //                        $lastDiagnosisRight = $x;
  //                    }
  //                }
                  $xaxis += self::X_AXIS_DATE_WIDTH;
                }
            }
            $this->eyegraphDates .= "];";
        }
    }

}

?>
