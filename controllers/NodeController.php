<?php
namespace yiipal\node\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yiipal\base\controllers\BaseController;
use yiipal\cck\models\FieldModel;
use yiipal\node\models\Node;
/**
 * Book controller
 */
class NodeController extends BaseController
{
    public function init(){
        parent::init();
        $this->session->set('nodeType', $this->arg(0));
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    public function actionList(){
        $type = $this->arg(0);
        $dataProvider = new ActiveDataProvider([
            'query' => Node::findQuery($type),
            'pagination' => [
                'pageSize' => 10,
                'route' => Yii::$app->request->getPathInfo(),
            ],
            'sort' => [
                'defaultOrder' => [
                    //'nid' => SORT_ASC,
                    //'company' => SORT_ASC,
                ]
            ],
        ]);
        //$dataProvider->getModels();
        return $this->render('@yiipal/node/views/node/index', [
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionUpdate()
    {
        $nodeType = $this->arg(0);
        $id = Yii::$app->request->get('id');
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/node/list/'.$nodeType]);
        } else {
            return $this->render('@yiipal/node/views/node/update', [
                'model' => $model,
            ]);
        }
        return $this->render('update');
    }

    public function actionCreate()
    {
        $nodeType = $this->arg(0);
        //FIXME:检查类型是否存在
        if(empty($nodeType)){
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model = new Node($nodeType);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/node/list/'.$nodeType]);
        } else {
            return $this->render('@yiipal/node/views/node/update', [
                'model' => $model,
            ]);
        }
        return $this->render('update');
    }

    /**
     * Deletes an existing model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $nodeType = $this->arg(0);
        $fields = FieldModel::findAll(['collection'=>"field.node.$nodeType"]);
        foreach($fields as $field){
            $fieldClass = $field->data_field_class;
            $fieldClass::$tableName = $field->name;
            $fieldModel = $fieldClass::findOne(['nid'=>$id]);
            if($fieldModel){
                $fieldModel->delete();
            }
        }
        $this->findModel($id)->delete();
        return $this->redirect(['/node/list/'.$nodeType]);
    }
}
