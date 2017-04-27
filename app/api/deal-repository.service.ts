import { Injectable } from '@angular/core';
import { Http, Headers, Response, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/toPromise';

import { Deal } from './deal';

@Injectable()
export class DealRepository {
    private _apiUrl = 'api/deals';
    private _testApiUrl = 'http://54.70.252.84/api/deals/test';

	private _deals: Deal[];

	private getIndex(id : number){
		for (var i = this._deals.length; i--;) {
			var deal = this._deals[i];
			if(deal.id == id) return i;
		}
		return -1;
	}

    constructor(private http: Http){
		this._deals = [
			{ id: 1, title: 'Deal 1', description: "SAMPLE DESCRIPTION", imagePath: 'img/tilted.png', rating: 20 },
			{ id: 2, title: 'Deal 2', description: "SAMPLE DESCRIPTION", imagePath: 'img/unknown.png', rating: 35 },
			{ id: 3, title: 'Deal 3', description: "SAMPLE DESCRIPTION", imagePath: 'img/wat.png', rating: 10 }
		];
    }

    public list(): Promise<Deal[]> {
        return this.http
            .get(this._testApiUrl)
            .toPromise()
            .then(x => {
                let body = x.json();
                return (body.data || body) as Deal[];
            })
            .catch(x => x.message);
    }

	public listInternal() : Deal[] {
		return this._deals;
    }

    public get(id: number): Promise<Deal> {
        return this.http
            .get(`${this._apiUrl}/${id}`)
            .toPromise()
            .then(x => x.json().data as Deal)
            .catch(x => x.message);
    }

	public getInternal(id : number) : Deal {
		var index = this.getIndex(id);
		return this._deals[index];
    }

    public add(deal: Deal): Promise<Deal> {
        return this.http
            .post(this._apiUrl, deal)
            .toPromise()
            .then(x => x.json().data as Deal)
            .catch(x => x.message);
    }

	public addInternal(deal: Deal) {
		deal.id = this._deals.length + 1;
		this._deals.push(deal);
    }

    public update(deal: Deal): Promise<Deal> {
        return this.http
            .put(`${this._apiUrl}/${deal.id}`, deal)
            .toPromise()
            .then(() => deal)
            .catch(x => x.message);
    }

	public updateInternal(deal: Deal) {
		var index = this.getIndex(deal.id);
		this._deals[index] = deal;
	}

    public delete(deal: Deal): Promise<void> {
        return this.http
            .delete(`${this._apiUrl}/${deal.id}`)
            .toPromise()
            .catch(x => x.message);
    }

	public deleteInternal(id : number) {
		var index = this.getIndex(id);
		this._deals.splice(index, 1);
    }
}