# QueryControlModelを用いたシンプルで強力なAPI

実際に実務で利用しており、GraphQLよりも柔軟で強力なAPIを実現しています。  
あとはPolicyやRequestバリデーションを設定するだけです。

## QueryControlModel
App\Models\Traitsに[QueryControlModel](/app/Models/Traits/QueryControlModel.php)を作成し、各モデルに継承させリクエストクエリから自由に検索、リレーションデータの追加をコントロールできるようカスタマイズしています。

```
namespace App\Models\Traits;

trait QueryControlModel
{
    /**
     * Append datas
     */
    public function scopeQueryControl($query)
    {
        return $query
        ->when(request()->query('select'), function ($query, $csv) {
            return $query->select(explode(',', $csv));
        })
        ->when(request()->query('with'), function ($query, $csv) {
            return $query->with(explode(',', $csv));
        })
        ->when(request()->query('withCount'), function ($query, $csv) {
            return $query->withCount(explode(',', $csv));
        });
    }

    /**
     * Please Override in Model
     */
    public function scopeQueryFilter($query)
    {
        return $query;
        // ex)
        // ->when(request()->query('user'), function ($query, $csv) {
        //     return $query->whereIn('user_id', explode(',', $csv));
        // })
    }

    /**
     * count(), paginate() and get() supported.
     */
    public function scopeQueryGet($query)
    {
        $count = request()->query('count', false);
        $paginate = request()->query('paginate', false);

        if ($count) {
            return $query->count();
        }
        if ($paginate) {
            return $query->paginate((int) $paginate);
        }
        return $query->get();
    }
}
```

QueryControlModelはRequestクエリにのみ反応するローカルスコープとして実装していますので、tinkerやバッチ処理の邪魔はしないようになっています。  
scopeQueryControl($query)は[各モデルでオーバーライド](./app/Models/Post.php)して使用します。


## コントローラーメソッドでの利用
indexやshowメソッドから呼び出して使用します。  

UserControllerの例
```
namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return User
        ::queryControl()// <-- withやwithCountのデータ追加
        ->queryFilter()//  <-- 各モデルのクエリフィルター
        ->queryGet();
    }

    public function show($id)// <-- インジェクションせず $id として取り出します
    {
        return User
        ::queryControl()// <-- withやwithCountのデータ追加
        ->queryFilter()//  <-- 各モデルのクエリフィルター
        ->findOrFail($id);
    }
}
```