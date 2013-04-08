<?php

class DefaultController extends BaseEventTypeController {

    public function actionView($id) {
        if (!$this->patient = Patient::model()->find('id=:id', array(':id' => $id))) {
            throw new CHttpException(403, 'Invalid patient id.');
        }

        $this->renderPartial(
                'view', array(
                ), false, true);
    }

}
