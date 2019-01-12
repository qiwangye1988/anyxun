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
namespace base;

/**
 * 例子：
 * CreateDir('a/1/2/3');                  建立文件夹 建一个a/1/2/3文件夹
 * CreateFile('b/1/2/3');                 建立文件        在b/1/2/文件夹下面建一个3文件
 * CreateFile('b/1/2/3.exe');             建立文件        在b/1/2/文件夹下面建一个3.exe文件
 * CopyDir('b','d/e');                    复制文件夹 建立一个d/e文件夹，把b文件夹下的内容复制进去
 * CopyFile('b/1/2/3.exe','b/b/3.exe');   复制文件        建立一个b/b文件夹，并把b/1/2文件夹中的3.exe文件复制进去
 * MoveDir('a/','b/c');                   移动文件夹 建立一个b/c文件夹,并把a文件夹下的内容移动进去，并删除a文件夹
 * MoveFile('b/1/2/3.exe','b/d/3.exe');   移动文件        建立一个b/d文件夹，并把b/1/2中的3.exe移动进去                   
 * UnlinkFile('b/d/3.exe');               删除文件        删除b/d/3.exe文件
 * UnlinkDir('d');                        删除文件夹 删除d文件夹
 */

/**
 * 操纵文件类
 * @author   Devil
 * @blog    http://gong.gg/
 * @version 1.0.0
 * @date    2018-06-28
 * @desc    支持所有文件存储到硬盘
 */
class FileUtil
{
    /**
     * 建立文件夹
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-06-29
     * @desc    description
     * @param   [string]     $aim_url   [目录地址]
     * @return  [boolean]               [true | false]
     */
    public static function CreateDir($aim_url)
    {
        $aim_url = str_replace('', '/', $aim_url);
        $aim_dir = '';
        $arr = explode('/', $aim_url);
        $result = true;
        foreach($arr as $str)
        {
            $aim_dir .= $str . '/';
            if(!file_exists($aim_dir))
            {
                $result = mkdir($aim_dir);
            }
        }
        return $result;
    }

    /**
     * 建立文件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-06-29
     * @desc    description
     * @param   [string]     $aim_url       [目录地址]
     * @param   [boolean]    $over_write    [该参数控制是否覆盖原文件]
     * @return  [boolean]                   [true | false]
     */
    public static function CreateFile($aim_url, $over_write = false)
    {
        if(file_exists($aim_url) && $over_write == false)
        {
            return false;
        } elseif(file_exists($aim_url) && $over_write == true)
        {
            self::UnlinkFile($aim_url);
        }
        $aim_dir = dirname($aim_url);
        self::CreateDir($aim_dir);
        touch($aim_url);
        return true;
    }

    /**
     * 移动文件夹
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-06-29
     * @desc    description
     * @param   [string]     $old_dir       [原地址]
     * @param   [string]     $aim_dir       [新地址]
     * @param   [boolean]    $over_write    [该参数控制是否覆盖原文件]
     * @return  [boolean]                   [true | false]
     */
    public static function MoveDir($old_dir, $aim_dir, $over_write = false)
    {
        $aim_dir = str_replace('', '/', $aim_dir);
        $aim_dir = substr($aim_dir, -1) == '/' ? $aim_dir : $aim_dir . '/';
        $old_dir = str_replace('', '/', $old_dir);
        $old_dir = substr($old_dir, -1) == '/' ? $old_dir : $old_dir . '/';
        if(!is_dir($old_dir))
        {
            return false;
        }
        if(!file_exists($aim_dir))
        {
            self::CreateDir($aim_dir);
        }
        @$dir_handle = opendir($old_dir);
        if(!$dir_handle)
        {
            return false;
        }
        while(false !== ($file = readdir($dir_handle)))
        {
            if($file == '.' || $file == '..')
            {
                continue;
            }
            if(!is_dir($old_dir . $file))
            {
                self::MoveFile($old_dir . $file, $aim_dir . $file, $over_write);
            } else {
                self::MoveDir($old_dir . $file, $aim_dir . $file, $over_write);
            }
        }
        closedir($dir_handle);
        return rmdir($old_dir);
    }

    /**
     * 移动文件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-06-29
     * @desc    description
     * @param   [string]     $file_url      [原文件]
     * @param   [string]     $aim_url       [新文件]
     * @param   [boolean]    $over_write    [该参数控制是否覆盖原文件]
     * @return  [boolean]                   [true | false]
     */
    public static function MoveFile($file_url, $aim_url, $over_write = false)
    {
        if(!file_exists($file_url))
        {
            return false;
        }
        if(file_exists($aim_url) && $over_write = false)
        {
            return false;
        } elseif(file_exists($aim_url) && $over_write = true)
        {
            self::UnlinkFile($aim_url);
        }
        $aim_dir = dirname($aim_url);
        self::CreateDir($aim_dir);
        rename($file_url, $aim_url);
        return true;
    }

    /**
     * 删除文件夹
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-06-29
     * @desc    description
     * @param   [string]     $aim_dir      [地址]
     * @return  [boolean]                  [true | false]
     */
    public static function UnlinkDir($aim_dir)
    {
        $aim_dir = str_replace('', '/', $aim_dir);
        $aim_dir = substr($aim_dir, -1) == '/' ? $aim_dir : $aim_dir . '/';
        if(!is_dir($aim_dir))
        {
            return false;
        }
        $dir_handle = opendir($aim_dir);
        while(false !== ($file = readdir($dir_handle)))
        {
            if($file == '.' || $file == '..')
            {
                continue;
            }
            if(!is_dir($aim_dir . $file))
            {
                self::UnlinkFile($aim_dir . $file);
            } else {
                self::UnlinkDir($aim_dir . $file);
            }
        }
        closedir($dir_handle);
        return rmdir($aim_dir);
    }

    /**
     * 删除文件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-06-29
     * @desc    description
     * @param   [string]     $aim_url      [文件]
     * @return  [boolean]                  [true | false]
     */
    public static function UnlinkFile($aim_url)
    {
        $aim_url = str_replace('//', '/', $aim_url);
        if(file_exists($aim_url))
        {
            unlink($aim_url);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 复制文件夹
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-06-29
     * @desc    description
     * @param   [string]     $old_dir       [原地址]
     * @param   [string]     $aim_dir       [新地址]
     * @param   [boolean]    $over_write    [该参数控制是否覆盖原文件]
     * @return  [boolean]                   [true | false]
     */
    public static function CopyDir($old_dir, $aim_dir, $over_write = false)
    {
        $aim_dir = str_replace('', '/', $aim_dir);
        $aim_dir = substr($aim_dir, -1) == '/' ? $aim_dir : $aim_dir . '/';
        $old_dir = str_replace('', '/', $old_dir);
        $old_dir = substr($old_dir, -1) == '/' ? $old_dir : $old_dir . '/';
        if(!is_dir($old_dir))
        {
            return false;
        }
        if(!file_exists($aim_dir))
        {
            self::CreateDir($aim_dir);
        }
        $dir_handle = opendir($old_dir);
        while(false !== ($file = readdir($dir_handle)))
        {
            if($file == '.' || $file == '..')
            {
                continue;
            }
            if(!is_dir($old_dir . $file))
            {
                self::CopyFile($old_dir . $file, $aim_dir . $file, $over_write);
            } else {
                self::CopyDir($old_dir . $file, $aim_dir . $file, $over_write);
            }
        }
        closedir($dir_handle);
        return is_dir($aim_dir);
    }

    /**
     * 复制文件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-06-29
     * @desc    description
     * @param   [string]     $file_url      [原文件]
     * @param   [string]     $aim_url       [新文件]
     * @param   [boolean]    $over_write    [该参数控制是否覆盖原文件]
     * @return  [boolean]                   [true | false]
     */
    public static function CopyFile($file_url, $aim_url, $over_write = false)
    {
        if(!file_exists($file_url))
        {
            return false;
        }
        if(file_exists($aim_url) && $over_write == false)
        {
            return false;
        } elseif(file_exists($aim_url) && $over_write == true)
        {
            self::UnlinkFile($aim_url);
        }
        $aim_dir = dirname($aim_url);
        self::CreateDir($aim_dir);
        copy($file_url, $aim_url);
        return true;
    }
}
?>