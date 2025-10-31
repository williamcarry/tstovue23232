import './bootstrap.js';
/*
 * Admin JavaScript entry file
 * 管理员后台入口文件
 */
import './styles/app.css';
import './styles/admin.css';  // 管理员后台专用样式
import $ from 'jquery';
window.$ = window.JQuery = $;

// 添加Vue控制器组件注册
import { registerVueControllerComponents } from '@symfony/ux-vue';

// 引入 Element Plus 样式
import 'element-plus/dist/index.css';

// 注册Vue控制器组件 - 必须从./vue/controllers/开始，这样组件名才能是admin/AdminLayout
registerVueControllerComponents(require.context('./vue/controllers/', true, /\.vue$/));
