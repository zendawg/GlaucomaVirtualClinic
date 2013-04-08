<?php

class SummaryController extends BaseEventTypeController {

    public function actionCreate() {
        parent::actionCreate();
    }

    public function actionUpdate($id) {
        parent::actionUpdate($id);
    }

    public function actionView($id) {
        if (!$this->patient = Patient::model()->find('id=:id', array(':id' => $id))) {
            throw new CHttpException(403, 'Invalid patient id.');
        }

        $this->renderPartial(
                'view', array(
                ), false, true);
    }

}
