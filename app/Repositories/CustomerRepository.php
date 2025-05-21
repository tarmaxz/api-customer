<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\CustomerTemperatureRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Integrations\BrasilApi;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CustomerRepository extends AbstractRepository {

    protected $model = Customer::class;
    protected $customerTemperatureRepository = CustomerTemperatureRepository::class;

    public function __construct(CustomerTemperatureRepository $customerTemperatureRepository)
    {
        $this->customerTemperatureRepository = $customerTemperatureRepository;
        parent::__construct();
    }

    public function filter($params, $query = null): Builder
    {
        if ($query == null) {
            $filter = $this->model->query();

            $filter = $query;

        } else {

            if (!empty($params['filter_name_full'])) {
                $name = $params['filter_name_full'];
                $query->where('name_full', 'like', "%$name%");
            }

            if (!empty($params['filter_cpf'])) {
                $cpf = $params['filter_cpf'];
                $query->where('cpf', $cpf);
            }

            if (!empty($params['filter_cep'])) {
                $cep = $params['filter_cep'];
                $query->where('zip_code', $cep);
            }

            $filter = $query;
        }

        return $filter;
    }

    public function all($params)
    {
        $data = Cache::get('list_customer', null);
        if (!empty($data)) {
            Log::info('com cache');
            return $data;
        }

        Log::info('sem cache');

        $query = $this->model::with(['customer_temperature'])->orderBy('id', 'DESC');

        $filter = $this->filter($params, $query);

        $paginateData = $this->paginate($filter, $params);

        Cache::put('list_customer', $paginateData, now()->addMinute(10));

        return $paginateData;
    }

    public function create(array $data)
    {
        $responseCustomer = $this->model::where('email', $data['email'])->whereOr('cpf', $data['cpf'])->first();

        $this->customerTemperatureRepository->validatorCustomerTemperature($data['customer_temperature_id'] ?? null);

        if ($responseCustomer) {
            throw new BusinessException('O cliente já está cadastrado');
        }
        
        $response = BrasilApi::getCepV2($data['zip_code']);

        if (!empty($response['cep'])) {
            $data['zip_code'] = $response['cep'];
        }

        if (!empty($response['state'])) {
            $data['state'] = $response['state'];
        }

        if (!empty($response['city'])) {
            $data['city'] = $response['city'];
        }

        if (!empty($response['neighborhood'])) {
            $data['neighborhood'] = $response['neighborhood'];
        }

        if (!empty($response['street'])) {
            $data['street'] = $response['street'];
        }

        return $this->model::create($data);
    }

    public function find($id)
    {
        $response = $this->model::find($id);

        if (!$response) {
            throw new BusinessException('Cliente não encontrado.');
        }
        return $response;
    }

    public function update($id,array $data)
    {
        $responseData = $this->find($id);

        $this->customerTemperatureRepository->validatorCustomerTemperature($data['customer_temperature_id'] ?? null);

        if ($data['zip_code'] !== $responseData->zip_code) {

            $response = BrasilApi::getCepV2($data['zip_code']);

            if (!empty($response['zip_code)'])) {
                $data['zip_code'] = $response['cep'];
            }
    
            if (!empty($response['state'])) {
                $data['state'] = $response['state'];
            }
    
            if (!empty($response['city'])) {
                $data['city'] = $response['city'];
            }
    
            if (!empty($response['neighborhood'])) {
                $data['neighborhood'] = $response['neighborhood'];
            }
    
            if (!empty($response['street'])) {
                $data['street'] = $response['street'];
            }
        }

        $responseData->update($data);
        $responseData->load(['customer_temperature']);

        return $responseData;
    }

    public function delete($id)
    {
        $response = $this->find($id);
        $response->delete();
        return $response;
    }
}