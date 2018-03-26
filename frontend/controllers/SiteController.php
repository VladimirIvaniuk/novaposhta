<?php
namespace frontend\controllers;

use LisDev\Delivery\NovaPoshtaApi2;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\helpers\ArrayHelper;
use Yii;
use frontend\models\NP;
use frontend\models\Cities;
define ("KEY", "d9e9aae440a15c718daa93d5fa7dceae");
/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $np=new \LisDev\Delivery\NovaPoshtaApi2(KEY);
        $cities = $np->getCities();
        return $this->render('index',[
            'np'=>$np,
            'cities'=>$cities,
        ]);
    }
    public function actionTest()
    {
        $np=new \LisDev\Delivery\NovaPoshtaApi2(KEY);
        if($_POST['sender_cities']) {
            $session = Yii::$app->session;
            $sender_city=$_POST['sender_cities'];
            $session->set('sender_cities', $sender_city);

            $wh = $np->getWarehouses($_POST['sender_cities']);
            foreach ($wh['data'] as $warehouse) {
                echo '<option value=."'.$warehouse['Ref'].'">'.$warehouse['DescriptionRu'].'</option>';
            }
        }
        if($_POST['recipient_city']) {
            $session2 = Yii::$app->session;
            $recipient_city=$_POST['recipient_city'];
            $session2->set('recipient_city', $recipient_city);
            $wh2 = $np->getWarehouses($_POST['recipient_city']);

            foreach ($wh2['data'] as $warehouse) {
                echo '<option value=."'.$warehouse['Ref'].'">'.$warehouse['DescriptionRu'].'</option>';
            }
        }
        if($_POST['recipient_city_ref']){
            $session = Yii::$app->session;
            $sender=$session->get('sender_cities');
            $session2 = Yii::$app->session;
            $recipient=$session2->get('recipient_city');

            $date = date('d.m.Y');

            $result = $np->getDocumentDeliveryDate($sender, $recipient, 'WarehouseWarehouse', $date);
            $my_res= $result['data'][0]['DeliveryDate']['date'];
            $rest = substr($my_res, 0, 10);
            $weight = 2;
// Цена в грн
            $price = 200;
// Получение стоимости доставки груза с указанным весом и стоимостью между складами в разных городах
            $result = $np->getDocumentPrice($sender, $recipient, 'WarehouseWarehouse', $weight, $price);
            echo 'Оринтеровочная дата доставки: '.$rest;
            echo '<br>';
            echo 'Цена: '.$result['data'][0]['Cost']. ' грн.';
        }
        $session->close();
        $session->destroy();
        $session2->close();
        $session2->destroy();
        exit();
    }
    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
