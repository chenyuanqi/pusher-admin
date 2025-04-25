<?php

namespace App\Http\Controllers;

use App\Services\TransformService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransformController extends Controller
{
    /**
     * @var TransformService
     */
    protected $transformService;

    /**
     * 构造函数
     *
     * @param TransformService $transformService
     */
    public function __construct(TransformService $transformService)
    {
        $this->transformService = $transformService;
    }

    /**
     * 执行文案转换
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transform(Request $request)
    {
        // 验证请求数据
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'type' => 'required|string',
            'options' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '请求参数验证失败',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 400);
        }

        try {
            $result = $this->transformService->transform(
                $request->input('content'),
                $request->input('type'),
                $request->input('options', [])
            );

            return response()->json([
                'code' => 200,
                'message' => '转换成功',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    /**
     * 获取转换类型列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTypes()
    {
        $types = config('admin.transform.types');

        return response()->json([
            'code' => 200,
            'message' => '操作成功',
            'data' => $types,
        ]);
    }

    /**
     * 模拟获取转换历史记录
     *
     * 注意：这里只是模拟实现，没有真正的数据库存储
     * 实际项目中应该从数据库中获取数据
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        // 模拟历史数据
        $mockData = $this->getMockHistoryData();

        // 计算分页数据
        $offset = ($page - 1) * $limit;
        $items = array_slice($mockData, $offset, $limit);
        $total = count($mockData);

        return response()->json([
            'code' => 200,
            'message' => '操作成功',
            'data' => [
                'items' => $items,
                'total' => $total,
                'page' => (int)$page,
                'limit' => (int)$limit,
            ],
        ]);
    }

    /**
     * 模拟获取单条历史记录详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistoryDetail($id)
    {
        $mockData = $this->getMockHistoryData();

        // 查找匹配的记录
        $detail = null;
        foreach ($mockData as $item) {
            if ($item['id'] == $id) {
                $detail = $item;
                break;
            }
        }

        if (!$detail) {
            return response()->json([
                'code' => 404,
                'message' => '记录不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '操作成功',
            'data' => $detail,
        ]);
    }

    /**
     * 模拟删除历史记录
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteHistory($id)
    {
        // 在实际应用中，这里应该删除数据库中的记录
        return response()->json([
            'code' => 200,
            'message' => '删除成功',
            'data' => null,
        ]);
    }

    /**
     * 模拟批量删除历史记录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchDeleteHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '请求参数验证失败',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 400);
        }

        $ids = $request->input('ids');

        // 在实际应用中，这里应该批量删除数据库中的记录
        return response()->json([
            'code' => 200,
            'message' => '批量删除成功',
            'data' => [
                'deleted_count' => count($ids),
            ],
        ]);
    }

    /**
     * 生成模拟历史数据
     *
     * @return array
     */
    private function getMockHistoryData()
    {
        return [
            [
                'id' => 1,
                'original_content' => '这个产品真的很好，我很喜欢。',
                'transformed_content' => '这个产品真的很好👍😊，我超喜欢❤️❤️。',
                'type' => 'emoji',
                'options' => ['level' => 2],
                'created_at' => '2023-10-01 15:30:00',
                'user' => [
                    'id' => 1,
                    'username' => 'admin',
                ],
            ],
            [
                'id' => 2,
                'original_content' => '嗨，大家好，我搞定了这个项目，没问题。',
                'transformed_content' => '您好，各位好，我已完成了这个项目，没有问题。',
                'type' => 'formal',
                'options' => [],
                'created_at' => '2023-10-01 14:20:00',
                'user' => [
                    'id' => 1,
                    'username' => 'admin',
                ],
            ],
            [
                'id' => 3,
                'original_content' => '这款产品的功能很强大，满足了我们的所有需求。',
                'transformed_content' => "这款产品的功能很强大，满足了我们的所有需求。\n\n记得点赞关注 @账号名称",
                'type' => 'social',
                'options' => ['platform' => 'douyin'],
                'created_at' => '2023-10-01 13:45:00',
                'user' => [
                    'id' => 1,
                    'username' => 'admin',
                ],
            ],
            [
                'id' => 4,
                'original_content' => '您好，请问这个功能是否可以按照需求进行定制呢？',
                'transformed_content' => '嗨，问下这个功能行不行按照需求定制啊？',
                'type' => 'casual',
                'options' => [],
                'created_at' => '2023-10-01 11:10:00',
                'user' => [
                    'id' => 1,
                    'username' => 'admin',
                ],
            ],
            [
                'id' => 5,
                'original_content' => '我们需要开展一场有趣的活动来增加用户粘性。',
                'transformed_content' => "我们需要开展一场有趣的活动来增加用户粘性。\n\n#热门话题# #每日分享#",
                'type' => 'social',
                'options' => ['platform' => 'weibo'],
                'created_at' => '2023-09-30 16:50:00',
                'user' => [
                    'id' => 1,
                    'username' => 'admin',
                ],
            ],
        ];
    }
}
