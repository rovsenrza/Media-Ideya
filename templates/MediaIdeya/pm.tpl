<article class="box story">
	<div class="box_in">
		<h1 class="title h1">{{Personal messages}}</h1>
		<div class="pm-box">
			<nav id="pm-menu">
				[inbox]<span>{{Messages list}}</span>[/inbox]
				[new_pm]<span>{{New message}}</span>[/new_pm]
			</nav>
			<div class="pm_status">
				{pm-progress-bar}
				{proc-pm-limit} % / ({pm-limit} {{pm messages}})
			</div>
		</div>
[pmlist]
<div class="pmlist">
	{pmlist}
</div>
[/pmlist]

[newpm]
<h4 class="heading">{{Create New message}}</h4>
<div class="addform addpm">
	<ul class="ui-form">
		<li class="form-group combo">
			<div class="combo_field">
				<input placeholder="{{Recipient name}}" type="text" name="name" value="{author}" class="wide" required>
			</div>
			<div class="combo_field">
				<input placeholder="{{Message subject}}" type="text" name="subj" value="{subj}" class="wide" required>
			</div>
		</li>
		<li id="comment-editor">{editor}</li> 
	[recaptcha]
		<li>{recaptcha}</li>
	[/recaptcha]
	[question]
		<li class="form-group">
			<label for="question_answer">{{Question}}: {question}</label>
			<input placeholder="{{Answer}}" type="text" name="question_answer" id="question_answer" class="wide" required>
		</li>
	[/question]
	</ul>
	<div class="form_submit">
		[sec_code]
			<div class="c-captcha">
				{sec_code}
				<input placeholder="{{Enter the code}}" title="{{Enter the code from the image}}" type="text" name="sec_code" id="sec_code" required>
			</div>
		[/sec_code]
		<button class="btn btn-big" type="submit" name="add"><b>{{Submit}}</b></button>
		<button class="btn-border btn-big" type="button" onclick="dlePMPreview()">{{Preview}}</button>
	</div>
</div>
[/newpm]

	</div>
</article>

[readpm]
<div class="comments">
	<div class="box">
		<div class="box_in">
			<h4>{subj}</h4>
			[not-self-dialog]{{Correspondence with the user}}: {user-dialog}[/not-self-dialog]
		</div>
		<div class="com_list">
[messages]
<div class="comment">
	<div class="com_info">
		<div class="avatar">
			<span class="cover" style="background-image: url({foto});">{login}</span>
			[online]<span class="com_online" title="{login} - {{Online}}">{{Online}}</span>[/online]
		</div>
		<div class="com_user">
			<b class="name">{author}</b>
			<span class="grey">
				{{write}}: {date}
			</span>
		</div>
		<div class="meta">
			<ul class="left">
				<li class="edit_btn" title="{{Edit}}">
					[pm-edit]<i title="{{Edit}}">{{Edit}}</i>[/pm-edit]
				</li>
				<li class="reply grey" title="{{Quote}}">
					[reply]<svg class="icon icon-reply"><use xlink:href="#icon-coms"></use></svg><span>{{Quote}}</span>[/reply]
				</li>
				<li class="reply grey" title="{{Ignore}}">
					[ignore]<svg class="icon icon-reply"><use xlink:href="#icon-dislike"></use></svg><span>{{Ignore}}</span>[/ignore]
				</li>
				<li class="complaint" title="{{Complaint}}">
					[complaint]<svg class="icon icon-bad"><use xlink:href="#icon-bad"></use></svg><span class="title_hide">{{Complaint}}</span>[/complaint]
				</li>
				<li class="del" title="{{Delete}}">
					[del]<svg class="icon icon-cross"><use xlink:href="#icon-cross"></use></svg><span class="title_hide">{{Delete}}</span>[/del]
				</li>
			</ul>
		</div>
	</div>
	<div class="com_content">
		<div class="text">{text}</div>
		[signature]<div class="signature">--------------------<br>{signature}</div>[/signature]
	</div>
</div>
[/messages]
		</div>
<div class="box_in form-sep">
	<h3>{{Reply to a message}}</h3>
	{editor}
	<br><button class="btn btn-big" type="submit" name="submit" title="{{Reply}}"><b>{{Reply}}</b></button>
</div>
	</div>
</div>
[/readpm]