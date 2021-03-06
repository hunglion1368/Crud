import { Component, OnInit } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { Store } from '@ngrx/store';
import { TranslateService } from 'ng2-translate';
import { Observable } from 'rxjs/Observable';
import * as _ from 'lodash';

import * as fromRoot from './../../../reducers';
import * as appMessage from './../../../core/reducers/app-message.reducer';
import * as {{ $reducer = camel_case($gen->entityName()).'Reducer' }} from './../../reducers/{{ $gen->slugEntityName() }}.reducer';
import * as {{ $actions = camel_case($gen->entityName()).'Actions' }} from './../../actions/{{ $gen->slugEntityName() }}.actions';
import { {{ $entitySin = $gen->entityName() }} } from './../../models/{{ camel_case($entitySin) }}';

{{ '@' }}Component({
  selector: '{{ str_replace(['.ts', '.'], ['', '-'], $gen->containerFile('list-and-search', true)) }}',
  templateUrl: './{{ $gen->containerFile('list-and-search-html', true) }}',
  styleUrls: ['./{{ $gen->containerFile('list-and-search-css', true) }}']
})
export class {{ $gen->containerClass('list-and-search', $plural = true) }} implements OnInit {
	
  public appMessages$: Observable<appMessage.State>;
	public {{ $state = camel_case($gen->entityName()).'State$' }}: Observable<{{ $reducer.'.State' }}>;

  /**
   * The search query options.
   */
  public queryData: Object = {
    // here we decide what columns to retrive from API
    filter: [
@foreach ($fields as $field)
@if ($field->on_index_table && !$field->hidden)
      '{{ $gen->tableName.'.'.$field->name }}',
@endif
@endforeach
    ],
    // the relations map, you know, we need some fields for eager load certain relations
    include: {
@foreach ($fields as $field)
@if ($field->namespace && !$field->hidden)
      '{{ $gen->tableName.'.'.$field->name }}': '{{  $gen->relationNameFromField($field)  }}',
@endif
@endforeach
    },
    orderBy: "{{ $gen->tableName.'.created_at' }}",
    sortedBy: "desc",
    page: 1
  };

  private title: string = 'module-name-plural';

  public constructor(
    private store: Store<fromRoot.State>,
    private titleService: Title,
    private translateService: TranslateService,
  ) {
    this.translateService
      .get('{{ $gen->entityNameSnakeCase() }}.' + this.title)
      .subscribe(val => this.titleService.setTitle(val));
  }

  public ngOnInit() {
    this.appMessages$ = this.store.select(fromRoot.getAppMessagesState);
  	this.{{ $state }} = this.store.select(fromRoot.get{{ $gen->entityName() }}State);
  	this.onSearch();
  }

  public onSearch(data: Object = {}) {
    this.queryData = _.merge({}, this.queryData, data);
    this.store.dispatch(new {{ $actions }}.LoadAction(this.queryData));
  }

  deleteRow(id: string) {
    this.store.dispatch(new {{ $actions }}.DeleteAction(id));
  }
}
