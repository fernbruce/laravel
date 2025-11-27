<?php

namespace App\Services;

use App\Constant;
use App\Models\Comment;
use App\Models\User\User;
use App\Services\User\UserServices;
use Illuminate\Support\Arr;

class CommentServices extends BaseServices
{

    public function getCommentByGoodsId($goodsId, $page = 1, $limit = 2, $sort = 'add_time', $order = 'desc')
    {
        return Comment::query()->where('value_id', $goodsId)->where('type', Constant::COMMENT_TYPE_GOODS)
            // ->forPage($page, $limit)->get();
            ->orderBy($sort, $order)
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function getCommentWithUserInfo($goodsId, $page = 1, $limit = 2)
    {
        $comments = $this->getCommentByGoodsId($goodsId, $page, $limit);
        $userIds = Arr::pluck($comments->items(), 'user_id');
        $userIds = array_unique($userIds);
        $users = UserServices::getInstance()->getUsers($userIds)->keyBy('id');
        $data = collect($comments->items())->map(function (Comment $comment) use ($users) {
            /** @var User $user */
            $user = $users->get($comment->user_id);
            // $comment = Arr::only($comment, ['id', 'addTime', 'content', 'admin_content', 'pic_list']);
            // $comment['nickname'] = $user->nickname;
            // $comment['avatar'] = $user->avatar;
            // return $comment;
            // todo 是否需要把$comment转化成数组
            $commentArray = $comment->toArray();
            $commentArray['picList'] = $commentArray['picUrls'];
            $commentArray = Arr::only($commentArray,['id','addTime','content','adminContent','picList']);
            $commentArray['nickname'] = $user->nickname??'';
            $commentArray['avatar'] = $user->avatar??'';
            return $commentArray;
//            return [
//                'id' => $comment->id,
//                'addTime' => $comment->add_time,
//                'content' => $comment->content,
//                'adminContent' => $comment->admin_content,
//                'picList' => $comment->pic_urls,
//                'nickname' => $user->nickname ?? '',
//                'avatar' => $user->avatar ?? ''
//            ];
        });
        return ['count' => $comments->total(), 'data' => $data];
    }
}
