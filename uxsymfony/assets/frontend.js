import './bootstrap.js';
/*
 * Frontend (Shop) JavaScript entry file
 * 商城前台入口文件
 */
import './styles/app.css';
import $ from 'jquery';
window.$ = window.JQuery = $;

// 添加Vue控制器组件注册
import { registerVueControllerComponents } from '@symfony/ux-vue';

// 引入 Element Plus 样式
import 'element-plus/dist/index.css';

// 注册Vue控制器组件 - 必须从./vue/controllers/开始，这样组件名才能是shop/ComponentName
registerVueControllerComponents(require.context('./vue/controllers/', true, /\.vue$/));
