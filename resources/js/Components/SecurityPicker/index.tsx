import { useForm } from '@inertiajs/inertia-react';
import { AxiosResponse } from 'axios';
import { debounce } from 'lodash';
import { useState, useEffect, useCallback } from 'react';
import { toast } from 'react-toastify';

import ChartSelect from '@/Components/ChartSelect';
import FormModal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Portfolio } from '@/Types/Shared';

export default function SecurityPicker({
  defaultValue,
  isMulti,
  canSavePortfolio,
  portfolioToUpdate,
  onPortfolioUpdate,
  placeholder = 'Search for securities...',
  className = 'py-2',
  value,
  handleChange,
}: {
  defaultValue?: any;
  isMulti?: boolean;
  canSavePortfolio?: boolean;
  portfolioToUpdate?: Portfolio;
  onPortfolioUpdate?: any;
  placeholder?: string;
  className?: string;
  value?: any;
  handleChange: any;
}) {
  const [selectedSecurities, setSelectedSecurities] = useState<any>(defaultValue);
  const [createModalIsOpen, setCreateModalIsOpen] = useState<boolean>(false);
  const [deleteModalIsOpen, setDeleteModalIsOpen] = useState<boolean>(false);

  const createFormProps = useForm({
    name: '',
    security_ids: null,
  });

  const deleteFormProps = useForm({
    portfolio_id: null,
  });

  useEffect(() => {
    if (value) {
      setSelectedSecurities(value);
    }
  }, [value]);

  useEffect(() => {
    createFormProps.setData(
      'security_ids',
      isMulti ? selectedSecurities?.map((s: any) => s.value) : selectedSecurities && selectedSecurities[0]?.value
    );
    if (selectedSecurities) {
      handleChange(selectedSecurities);
    }
  }, [selectedSecurities]);

  const getSecurityOptions = useCallback(
    debounce((input, callback) => {
      window.axios
        .get(window.route('securities.search'), {
          params: {
            q: input,
          },
        })
        .then((res: AxiosResponse) => {
          callback(res.data);
        });
    }, 250),
    []
  );

  const createPortfolio = (e: any) => {
    e.preventDefault();

    const portfolioRoute = portfolioToUpdate
      ? window.route('portfolios.update', portfolioToUpdate?.id)
      : window.route('portfolios.store');
    const portfolioMethod = portfolioToUpdate ? createFormProps.put : createFormProps.post;

    portfolioMethod(portfolioRoute, {
      onSuccess: (r: any) => {
        setCreateModalIsOpen(false);
        if (onPortfolioUpdate) {
          onPortfolioUpdate(r?.props?.portfolios);
        }
        toast.success(r?.props?.flash?.message);
      },
    });
  };

  const deletePortfolio = (e: any) => {
    e.preventDefault();

    deleteFormProps.delete(window.route('portfolios.destroy', portfolioToUpdate?.id), {
      onSuccess: (r: any) => {
        setDeleteModalIsOpen(false);
        if (onPortfolioUpdate) {
          onPortfolioUpdate(r?.props?.portfolios);
        }
        toast.success(r?.props?.flash?.message);
      },
    });
  };

  const select = (
    <ChartSelect
      className="grow"
      isAsync
      isMulti={isMulti}
      placeholder={placeholder}
      defaultValue={selectedSecurities}
      onChange={setSelectedSecurities}
      loadOptions={getSecurityOptions}
      {...(value && { value: value })}
    />
  );

  if (canSavePortfolio && (portfolioToUpdate || selectedSecurities?.length)) {
    return (
      <div className={`flex ${className}`}>
        <FormModal
          title={`${portfolioToUpdate ? 'Update' : 'Create'} Portfolio`}
          isOpen={createModalIsOpen}
          closeModal={() => setCreateModalIsOpen(false)}
          body={
            <div>
              <form onSubmit={createPortfolio}>
                <TextInput
                  required
                  label="Name:"
                  value={createFormProps.data.name}
                  handleChange={(e: any) => createFormProps.setData('name', e.target.value)}
                />
                <PrimaryButton className="mt-3 float-right" processing={createFormProps.processing}>
                  {portfolioToUpdate ? 'Update' : 'Create'}
                </PrimaryButton>
              </form>
            </div>
          }
        />
        <FormModal
          title="Delete Portfolio"
          isOpen={deleteModalIsOpen}
          closeModal={() => setDeleteModalIsOpen(false)}
          body={
            <div>
              <form onSubmit={deletePortfolio}>
                <p>Are you sure you want to delete the &ldquo;{portfolioToUpdate?.name}&rdquo; portfolio?</p>
                <PrimaryButton
                  backgroundColorClass="bg-red-800"
                  className="mt-3 float-right"
                  processing={deleteFormProps.processing}
                >
                  Delete
                </PrimaryButton>
              </form>
            </div>
          }
        />

        {select}
        <form className="flex" onSubmit={createPortfolio}>
          <PrimaryButton
            className="ml-3"
            type={portfolioToUpdate ? 'submit' : 'button'}
            onClick={() => portfolioToUpdate ?? setCreateModalIsOpen(true)}
            processing={createFormProps.processing}
          >
            {portfolioToUpdate ? 'Update' : 'Create'} Portfolio
          </PrimaryButton>
        </form>
        {portfolioToUpdate && (
          <PrimaryButton
            className="ml-3"
            type="button"
            backgroundColorClass="bg-red-800"
            onClick={() => setDeleteModalIsOpen(true)}
          >
            Delete
          </PrimaryButton>
        )}
      </div>
    );
  }

  return <div className={className}>{select}</div>;
}
