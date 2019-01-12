<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2018 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\service;

use think\Db;

/**
 * 主题服务层
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class ThemeService
{
    // 静态目录和html目录
    private static $html_path = 'application'.DS.'index'.DS.'view'.DS;
    private static $static_path = 'public'.DS.'static'.DS.'index'.DS;

    /**
     * 获取模板列表
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-05-10T10:24:40+0800
     * @param    [array]          $params [输入参数]
     * @return   [array]                  [模板列表]
     */
    public static function ThemeList($params = [])
    {
        $result = [];
        $dir = ROOT.self::$html_path;
        if(is_dir($dir))
        {
            if($dh = opendir($dir))
            {
                $default_preview = __MY_URL__.'static'.DS.'common'.DS.'images'.DS.'default-preview.jpg';
                while(($temp_file = readdir($dh)) !== false)
                {
                    $config = $dir.$temp_file.DS.'config.json';
                    if(!file_exists($config))
                    {
                        continue;
                    }

                    // 读取配置文件
                    $data = json_decode(file_get_contents($config), true);
                    if(!empty($data) && is_array($data))
                    {
                        if(empty($data['name']) || empty($data['ver']) || empty($data['author']))
                        {
                            continue;
                        }
                        $preview = ROOT.self::$static_path.$temp_file.DS.'images'.DS.'preview.jpg';
                        $result[] = array(
                            'theme'     =>  $temp_file,
                            'name'      =>  htmlentities($data['name']),
                            'ver'       =>  str_replace(array('，',','), ', ', htmlentities($data['ver'])),
                            'author'    =>  htmlentities($data['author']),
                            'home'      =>  isset($data['home']) ? $data['home'] : '',
                            'preview'   =>  file_exists($preview) ? __MY_URL__.'static'.DS.'index'.DS.$temp_file.DS.'images'.DS.'preview.jpg' : $default_preview,
                            'is_delete' => ($temp_file == 'default') ? 0 : 1,
                        );
                    }
                }
                closedir($dh);
            }
        }
        return $result;
    }

    /**
     * 模板上传
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-12-19T00:53:45+0800
     * @param    [array]          $params [输入参数]
     */
    public static function ThemeUpload($params = [])
    {
        // 文件上传校验
        $error = FileUploadError('theme');
        if($error !== true)
        {
            return DataReturn($error, -1);
        }

        // 文件格式化校验
        $type = array('application/zip', 'application/octet-stream');
        if(!in_array($_FILES['theme']['type'], $type))
        {
            return DataReturn('文件格式有误，请上传zip压缩包', -2);
        }

        // 目录是否有权限
        if(!is_writable(ROOT.self::$html_path))
        {
            return DataReturn('视图目录没权限', -10);
        }
        if(!is_writable(ROOT.self::$static_path))
        {
            return DataReturn('资源目录没权限', -10);
        }

        // 开始解压文件
        $resource = zip_open($_FILES['theme']['tmp_name']);
        while(($temp_resource = zip_read($resource)) !== false)
        {
            if(zip_entry_open($resource, $temp_resource))
            {
                // 当前压缩包中项目名称
                $file = zip_entry_name($temp_resource);

                // 排除临时文件和临时目录
                if(strpos($file, '/.') === false && strpos($file, '__') === false)
                {
                    // 拼接路径
                    if(strpos($file, '_html') !== false)
                    {
                        $file = ROOT.self::$html_path.$file;
                    } else if(strpos($file, '_static') !== false)
                    {
                        $file = ROOT.self::$static_path.$file;
                    } else {
                        continue;
                    }
                    $file = str_replace(array('_static/', '_html/'), '', $file);

                    // 截取文件路径
                    $file_path = substr($file, 0, strrpos($file, '/'));

                    // 路径不存在则创建
                    if(!is_dir($file_path))
                    {
                        mkdir($file_path, 0777, true);
                    }

                    // 如果不是目录则写入文件
                    if(!is_dir($file))
                    {
                        // 读取这个文件
                        $file_size = zip_entry_filesize($temp_resource);
                        $file_content = zip_entry_read($temp_resource, $file_size);
                        file_put_contents($file, $file_content);
                    }
                    // 关闭目录项  
                    zip_entry_close($temp_resource);
                }
            }
        }
        return DataReturn('操作成功');
    }

    /**
     * 模板删除
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-12-19T00:46:02+0800
     * @param    [array]          $params [输入参数]
     */
    public static function ThemeDelete($params = [])
    {
        if(empty($params['id']))
        {
            return DataReturn('模板id有误', -1);
        }
        // 防止路径回溯
        $id = htmlentities(str_replace(array('.', '/', '\\', ':'), '', strip_tags($params['id'])));
        if(empty($id))
        {
            return DataReturn('主题名称有误', -1);
        }

        // default不能删除
        if($id == 'default')
        {
            return DataReturn('系统模板不能删除', -2);
        }

        // 默认主题
        $theme = MyC('common_default_theme', 'default', true);

        // 不能删除正在使用的主题
        if($theme == $id)
        {
            return DataReturn('不能删除正在使用的主题', -2);
        }

        // 开始删除主题
        if(\base\FileUtil::UnlinkDir(ROOT.self::$html_path.$id) && \base\FileUtil::UnlinkDir(ROOT.self::$static_path.$id))
        {
            return DataReturn('删除成功');
        }
        return DataReturn('删除失败或资源不存在', -100);
    }
}
?>