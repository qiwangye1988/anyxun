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
namespace app\index\controller;

use app\service\GoodsService;

/**
 * 商品详情
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Goods extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 详情
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-12-02T23:42:49+0800
     */
    public function Index()
    {
        $id = input('id');
        $params = [
            'where' => [
                'id'    => $id,
                'is_delete_time' => 0,
            ],
            'is_photo'  => true,
            'is_spec'   => true,
        ];
        $goods = GoodsService::GoodsList($params);
        if(empty($goods[0]) || $goods[0]['is_delete_time'] != 0)
        {
            $this->assign('msg', '资源不存在或已被删除');
            return $this->fetch('/public/tips_error');
        } else {
            // 当前登录用户是否已收藏
            $ret_favor = GoodsService::IsUserGoodsFavor(['goods_id'=>$id, 'user'=>$this->user]);
            $goods[0]['is_favor'] = ($ret_favor['code'] == 0) ? $ret_favor['data'] : 0;

            // 商品评价总数
            $goods[0]['comments_count'] = GoodsService::GoodsCommentsTotal($id);

            // 商品收藏总数
            $goods[0]['favor_count'] = GoodsService::GoodsFavorTotal(['goods_id'=>$id]);

            $this->assign('goods', $goods[0]);
            $this->assign('home_seo_site_title', $goods[0]['title']);

            // 商品访问统计
            GoodsService::GoodsAccessCountInc(['goods_id'=>$id]);

            // 用户商品浏览
            GoodsService::GoodsBrowseSave(['goods_id'=>$id, 'user'=>$this->user]);

            // 左侧商品 看了又看
            $params = [
                'where'     => [
                    'is_delete_time'=>0,
                    'is_shelves'=>1
                ],
                'order_by'  => 'access_count desc',
                'field'     => 'id,title,title_color,price,images',
                'n'         => 10,
            ];
            $this->assign('left_goods', GoodsService::GoodsList($params));

            // 详情tab商品 猜你喜欢
            $params = [
                'where'     => [
                    'is_delete_time'=>0,
                    'is_shelves'=>1,
                    'is_home_recommended'=>1,
                ],
                'order_by'  => 'sales_count desc',
                'field'     => 'id,title,title_color,price,images,home_recommended_images',
                'n'         => 16,
            ];
            $this->assign('detail_like_goods', GoodsService::GoodsList($params));

            return $this->fetch();
        }
    }

    /**
     * 商品收藏
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-13
     * @desc    description
     */
    public function Favor()
    {
        // 是否登录
        $this->Is_Login();

        // 开始处理
        $params = input('post.');
        $params['user'] = $this->user;
        return GoodsService::GoodsFavor($params);
    }

    /**
     * 商品规格类型
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-14
     * @desc    description
     */
    public function SpecType()
    {
        // 开始处理
        $params = input('post.');
        return GoodsService::GoodsSpecType($params);
    }

    /**
     * 商品规格信息
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-12-14
     * @desc    description
     */
    public function SpecDetail()
    {
        // 开始处理
        $params = input('post.');
        return GoodsService::GoodsSpecDetail($params);
    }
}
