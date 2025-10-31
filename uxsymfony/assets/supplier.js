import './bootstrap.js';
/*
 * Supplier JavaScript entry file
 * 供应商后台入口文件
 */
import './styles/app.css';
import './styles/admin.css';  // 复用管理员后台样式（布局类似）
import $ from 'jquery';
window.$ = window.JQuery = $;

// 添加Vue控制器组件注册
import { registerVueControllerComponents } from '@symfony/ux-vue';

// 引入 Element Plus 样式
import 'element-plus/dist/index.css';

// 注册Vue控制器组件 - 必须从./vue/controllers/开始，这样组件名才能是supplier/ComponentName
registerVueControllerComponents(require.context('./vue/controllers/', true, /\.vue$/));
