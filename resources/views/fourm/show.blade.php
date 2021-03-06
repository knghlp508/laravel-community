@extends('app')
@section('content')
    <div class="jumbotron">
        <div class="container">
            <div class="media">
                <div class="media-left">
                    <a href="#">
                        <img src="{{ $discussion->user->avatar }}" alt="64x64" class="media-object img-circle" style="width: 64px;height: 64px;">
                    </a>
                </div>
                <div class="media-body">
                    <h4 class="media-heading">
                        {{ $discussion->title }}
                        @if(Auth::check() && Auth::user()->id==$discussion->user_id)
                            <a class="btn btn-primary btn-lg pull-right" href="/discussions/{{ $discussion->id }}/edit" role="button">修改帖子</a>
                        @endif
                    </h4>
                    {{ $discussion->user->name }}
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-9" role="main" id="post">
                <div class="blog-post">
                    {!! $html !!}
                </div>
                <hr>
                @foreach($discussion->comments as $comment)
                    <div class="media">
                        <div class="media-left">
                            <a href="#">
                                <img src="{{ $comment->user->avatar }}" alt="64x64" class="media-object img-circle" style="width: 64px;height: 64px">
                            </a>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">{{ $comment->user->name }}</h4>
                            {{ $comment->body }}
                        </div>
                    </div>
                @endforeach
                <div class="media" v-for="comment in comments">
                    <div class="media-left">
                        <a href="#">
                            <!-- 使用v-bind来绑定src，以免页面加载的时候加载图片 -->
                            <img src="@{{ comment.avatar }}" alt="64x64" class="media-object img-circle" style="width: 64px;height: 64px">
                        </a>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">@{{ comment.name }}</h4>
                        @{{ comment.body }}
                    </div>
                </div>
                <hr>
                @if(Auth::check())
                {!! Form::open(['url'=>'/comment','v-on:submit'=>'onSubmitForm']) !!}
                    {!! Form::hidden('discussion_id',$discussion->id) !!}
                    <div class="form-group">
                        {!! Form::textarea('body',null,['class'=>'form-control','v-model'=>'newComment.body']) !!}
                    </div>
                    <div>{!! Form::submit('发表评论',['class'=>'btn btn-success pull-right']) !!}</div>
                {!! Form::close() !!}
                @else
                    <a href="/user/login" class="btn btn-block btn-success">登录参与评论吧</a>
                @endif
            </div>
        </div>
    </div>
    <script type="text/javascript">
        Vue.http.headers.common['X-CSRF-TOKEN']=document.querySelector('#token').getAttribute('value');
        new Vue({
            el:'#post',
            data:{
                //评论的所有内容，用于循环输出所有评论
                comments:[],
                //用户评论
                newComment:{
                    name:'{{ Auth::user()->name }}',
                    avatar:'{{ Auth::user()->avatar }}',
                    //body与评论框进行数据双向绑定
                    body:''
                },
                //post请求提交的数据
                newPost:{
                    discussion_id:'{{ $discussion->id }}',
                    user_id:'{{ Auth::user()->id }}',
                    body:''
                }
            },
            methods:{
                onSubmitForm: function (e) {
                    //阻止表单提交
                    e.preventDefault();
                    var comment=this.newComment;
                    var post=this.newPost;
                    //将评论的内容添加到提交的post数据中的body
                    post.body=comment.body;
                    //进行ajaxPOST请求，提交评论，并且将用户的评论添加到所有评论内容
                    this.$http.post('/comment',post, function () {
                        this.comments.push(comment);
                    });
                    //初始化用户评论
                    this.newComment={
                        name:'{{ Auth::user()->name }}',
                        avatar:'{{ Auth::user()->avatar }}',
                        body:''
                    };
                }
            }
        });
    </script>
@stop