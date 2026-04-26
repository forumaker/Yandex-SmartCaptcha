import Extend from 'flarum/common/extenders';
import YandexSmartCaptchaPage from './components/YandexSmartCaptchaPage';

export default [new Extend.Admin().page(YandexSmartCaptchaPage)];