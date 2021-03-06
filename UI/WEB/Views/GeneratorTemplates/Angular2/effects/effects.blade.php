import { Injectable } from '@angular/core';
import { Actions, Effect } from '@ngrx/effects';
import { Action, Store } from '@ngrx/store';
import { Observable } from 'rxjs/Observable';
import { of } from 'rxjs/observable/of';
import { go } from '@ngrx/router-store';
import 'rxjs/add/operator/withLatestFrom'

import * as fromRoot from './../../reducers';
import * as appMsgActions from './../../core/actions/app-message.actions';
import { FormModelParserService } from './../../core/services/form-model-parser.service';
import { {{ ($entitySin = $gen->entityName()).'Pagination' }} } from './../models/{{ $camelEntity = camel_case($entitySin) }}Pagination';
import { {{ $entitySin }}Service } from './../services/{{ $gen->slugEntityName() }}.service';
import * as {{ $actions = camel_case($gen->entityName()) }} from './../actions/{{ $gen->slugEntityName() }}.actions';
import { {{ $entitySin = $gen->entityName() }} } from './../models/{{ camel_case($entitySin) }}';
import { AppMessage } from './../../core/models/appMessage';

@Injectable()
export class {{ $entitySin }}Effects {

	public constructor(
    private actions$: Actions,
    private {{ $service = camel_case($entitySin).'Service' }}: {{ $entitySin }}Service,
    private FormModelParserService: FormModelParserService,
    private store: Store<fromRoot.State>
  ) { }

  @Effect()
  load{{ $gen->entityName(true) }}$: Observable<Action> = this.actions$
    .ofType({{ $actions }}.ActionTypes.LOAD_{{ $entitySnakePlu = $gen->entityNameSnakeCase(true) }})
    .map((action: Action) => action.payload)
    .switchMap((searchData) => {
      return this.{{ $service }}.load(searchData)
        .map((data: {{ $entitySin.'Pagination' }}) => { return new {{ $actions }}.LoadSuccessAction(data)})
        .catch((error: AppMessage) => {
          error.type = 'danger';
          return of(new appMsgActions.Flash(error))
        });
    });

  @Effect()
  get{{ $camelEntity }}FormModel$: Observable<Action> = this.actions$
    .ofType({{ $actions }}.ActionTypes.GET_{{ $gen->entityNameSnakeCase() }}_FORM_MODEL)
    .withLatestFrom(this.store.select(fromRoot.get{{ $gen->entityName() }}State))
    .switchMap(([action, state]) => {
      // prevent API call if we have the form model already
      if (state.{{ camel_case($gen->entityName()) }}FormModel !== null) {
        return of(new {{ $actions }}.GetFormModelSuccessAction(state.{{ camel_case($gen->entityName()) }}FormModel));
      }

      return this.{{ $service }}.get{{ $gen->entityName() }}FormModel()
        .map((data) => this.FormModelParserService.parse(data, this.{{ $service }}.fieldsLangNamespace))
        .map((data) => { return new {{ $actions }}.GetFormModelSuccessAction(data)})
        .catch((error: AppMessage) => {
          error.type = 'danger';
          return of(new appMsgActions.Flash(error))
        });
    });

    @Effect()
    get{{ $camelEntity }}FormData$: Observable<Action> = this.actions$
      .ofType({{ $actions }}.ActionTypes.GET_{{ $gen->entityNameSnakeCase() }}_FORM_DATA)
      .withLatestFrom(this.store.select(fromRoot.get{{ $gen->entityName() }}State))
      .switchMap(([action, state]) => {
        // prevent API call if we have the form data already
        if (state.{{ camel_case($gen->entityName()) }}FormData !== null) {
          return of(new {{ $actions }}.GetFormDataSuccessAction(state.{{ camel_case($gen->entityName()) }}FormData));
        }

        return this.{{ $service }}.get{{ $gen->entityName() }}FormData()
          .map((data) => { return new {{ $actions }}.GetFormDataSuccessAction(data)})
          .catch((error: AppMessage) => {
            error.type = 'danger';
            return of(new appMsgActions.Flash(error))
          });
      });

    @Effect()
    get$: Observable<Action> = this.actions$
      .ofType({{ $actions }}.ActionTypes.GET_{{ $gen->entityNameSnakeCase() }})
      .withLatestFrom(this.store.select(fromRoot.get{{ $gen->entityName() }}State))
      .switchMap(([action, state]) => {
        // prevent API call if we have the data object already
        if (state.selected{{ $gen->entityName() }} && action.payload == state.selected{{ $gen->entityName() }}.id) {
          return of(new {{ $actions }}.SetSelectedAction(state.selected{{ $gen->entityName() }}));
        }

        return this.{{ $service }}.get{{ $gen->entityName() }}(action.payload)
          .mergeMap((data: {{ $entitySin }}) => {
            return [
              new {{ $actions }}.SetSelectedAction(data),
            ];
          })
          .catch((error: AppMessage) => {
            error.type = 'danger';
            return of(new appMsgActions.Flash(error));
          });
      });

    @Effect()
    create$: Observable<Action> = this.actions$
      .ofType({{ $actions }}.ActionTypes.CREATE_{{ $gen->entityNameSnakeCase() }})
      .map((action: Action) => action.payload)
      .switchMap((data) => {
        return this.{{ $service }}.create(data)
          .mergeMap((data: {{ $entitySin }}) => {
            return [
              new {{ $actions }}.SetSelectedAction(data),
              new appMsgActions.Flash(this.{{ $service }}.getSuccessMessage('create')),
              go(['{{ $gen->slugEntityName() }}', data.id, 'details'])
            ];
          })
          .catch((error: AppMessage) => {
            error.type = 'danger';
            return of(error).mergeMap(error => {
              let actions = [];
              actions.push(new appMsgActions.Flash(error));

              if (error.status_code === 422) {
                actions.push(new {{ $actions }}.SetErrorsAction(error.errors));
              }

              return actions;
            })
          });
      });

    @Effect()
    update$: Observable<Action> = this.actions$
      .ofType({{ $actions }}.ActionTypes.UPDATE_{{ $gen->entityNameSnakeCase() }})
      .map((action: Action) => action.payload)
      .switchMap((data: {{ $entitySin }}) => {
        return this.{{ $service }}.update(data)
          .mergeMap((data: {{ $entitySin }}) => {
            return [
              new {{ $actions }}.SetSelectedAction(data),
              new appMsgActions.Flash(this.{{ $service }}.getSuccessMessage('update')),
              go(['{{ $gen->slugEntityName() }}', data.id, 'details'])
            ];
          })
          .catch((error: AppMessage) => {
            error.type = 'danger';
            return of(new appMsgActions.Flash(error))
          });
      });

    @Effect()
    delete$: Observable<Action> = this.actions$
      .ofType({{ $actions }}.ActionTypes.DELETE_{{ $gen->entityNameSnakeCase() }})
      .map((action: Action) => action.payload)
      .switchMap(id => {
        return this.{{ $service }}.delete(id)
          .mergeMap(() => {
            return [
              new {{ $actions }}.LoadAction(),
              new appMsgActions.Flash(this.{{ $service }}.getSuccessMessage('delete')),
              go(['{{ $gen->slugEntityName() }}'])
            ];
          })
          .catch((error: AppMessage) => {
            error.type = 'danger';
            return of(new appMsgActions.Flash(error))
          });
      });
}
