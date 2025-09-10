<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

class ClaimUserDbService
{
    private Connection $connection;

    public function __construct(Connection $claimUserDbConnection)
    {
        $this->connection = $claimUserDbConnection;
    }

    /**
     * Liste des claims d'un utilisateur (dashboard)
     * 
     * @param array $params
     * @return array
     */
    public function callGetListByUser(array $params): array
    {
        /** @var \PDO $pdo */
        $pdo = $this->connection->getNativeConnection();

        $stmt = $pdo->prepare("CALL GetListByUserPag(?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bindValue(1, $params['p_email']);
        $stmt->bindValue(2, $params['p_status'] ?? '');
        $stmt->bindValue(3, $params['p_search_name'] ?? '');
        $stmt->bindValue(4, $params['p_sort_by'] ?? 'date');
        $stmt->bindValue(5, (int)($params['p_page'] ?? 1), \PDO::PARAM_INT);
        $stmt->bindValue(6, (int)($params['p_page_size'] ?? 10), \PDO::PARAM_INT);
        $stmt->bindValue(7, $params['p_search_num'] ?? '');
        $stmt->bindValue(8, $params['p_search_reg_num'] ?? '');
        $stmt->bindValue(9, $params['p_search_phone'] ?? '');

        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($stmt->nextRowset()) {
            $meta = $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            $meta = [];
        }

        return array_merge(
            ['data' => $data],
            $meta ?: []
        );

    }

    /**
     * Information utilisateur pour visualiser et gerer profile
     * 
     * @param array $params
     * @return array
     */
    public function callGetUserProfile(array $params): array
    {
        $sql = "CALL GetUserProfile(?)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_email_address']);
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Retourne tous les claims
     * 
     * @param array $params
     * @return array
     */
    public function callGetAllClaims(array $params) : array 
    {
        $sql = "CALL GetAllClaims(?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['page'], \PDO::PARAM_INT);
        $stmt->bindValue(2, $params['page_size'], \PDO::PARAM_INT);
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Retourne tous les roles
     * 
     * @param array $params
     * @return array
     */
    public function callAllRoles(array $params) : array 
    {
        $sql = "CALL GetAllRoles(?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['page'], \PDO::PARAM_INT);
        $stmt->bindValue(2, $params['page_size'], \PDO::PARAM_INT);
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Retourne les utilisateur par role
     * 
     * @param array $params
     * @return array
     */
    public function callGetUserByRole(array $params) : array {
        $sql = "CALL GetUserByRole(?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['role_id']);
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Mise à jour du site web de l'utilisateur
     * 
     * @param array $params
     * @return array
     */
    public function callUpdateUserWebsite(array $params) : array
    {
        $sql = "CALL UpdateUserWebsite(?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_email_address']);
        $stmt->bindValue(2, $params['p_new_website']);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Mise à jour des paramètres administratifs
     * 
     * @param array $params
     * @return array
     */
    public function callUpdateAdminSetting(array $params) : array
    {
        $sql = "CALL UpdateAdminSettings(?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_email_address']);
        $stmt->bindValue(2, $params['p_primary_contact_name'] ?? null);
        $stmt->bindValue(3, $params['p_primary_contact_post'] ?? null);
        $stmt->bindValue(4, $params['p_notification'] ?? null);
        $stmt->bindValue(5, $params['p_method_names'] ?? null);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Mise à jour du mot de passe utilisateur
     * 
     * @param array $params
     * @return array
     */
    public function callUpdateUserPassword(array $params) : array
    {
        $sql = "CALL UpdateUserPassword(?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_email_address']);
        $stmt->bindValue(2, $params['p_new_password'] ?? null);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Vérifie si l'email existe pour la récupération de mot de passe
     * 
     * @param array $params
     * @return array
     */
    public function callForgotPassword(array $params) : array
    {
        $sql = "CALL ChekEmailExists(?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_email_address']);

        return $stmt->executeQuery()->fetchAllAssociative();   
    }

    /**
     * Authentification de l'utilisateur
     * 
     * @param array $params
     * @return array
     */
    public function callAuthentification(array $params) : array         
    {
        $sql = "CALL AuthentificateUser(?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_email_address']);
        $stmt->bindValue(2, $params['p_password']);

        return $stmt->executeQuery()->fetchAllAssociative(); 
    }

    public function callPostAffectionClaim(array $params) : array         
    {
        $sql = "CALL InsertAssignment(?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_users_id']);
        $stmt->bindValue(2, $params['p_assignment_date'] ?? null);
        $stmt->bindValue(3, $params['p_assignement_note'] ?? null);
        $stmt->bindValue(4, $params['p_status_id'] ?? null);
        $stmt->bindValue(5, $params['p_claims_number']);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Retourne detail affectation
     * 
     * @param array $params
     * @return array
     */
    public function callGetAssignementFilter(array $params) : array 
    {
        $sql = "CALL GetAssignmentList(?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_claims_number'] ?? null);
        $stmt->bindValue(2, $params['p_status_name'] ?? null);
        $stmt->bindValue(3, $params['p_role_name'] ?? null);
        $stmt->bindValue(4, $params['p_business_name'] ?? null);
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Modification affectation
     * 
     * @param array $params
     * @return array
     */
    public function callUpdateAssignment(array $params) : array 
    {
        $sql = "CALL UpdateAssignment(?, ?, ?, ?, ? )";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_users_id']);
        $stmt->bindValue(2, $params['p_assignment_date'] ?? null);
        $stmt->bindValue(3, $params['p_assignement_note'] ?? null);
        $stmt->bindValue(4, $params['p_status_id'] ?? null);
        $stmt->bindValue(5, $params['p_claims_number']);
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * Retourne la liste des methodes de communication
     */
    public function callGetMethodCommunication() : array 
    {
        $sql = "CALL GetMethodCommunication()";

        $stmt = $this->connection->prepare($sql);
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }

     /**
     * Mise à jour security setting utilisateur
     * 
     * @param array $params
     * @return array
     */
    public function callUpdateUserSecurity(array $params) : array
    {
        $sql = "CALL UpdateSecuritySetting(?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_email_address']);
        $stmt->bindValue(2, $params['p_new_password'] ?? null);
        $stmt->bindValue(3, $params['p_new_backup_email'] ?? null);

        return $stmt->executeQuery()->fetchAllAssociative();
        
    }

    /**
     * Insertion utilisateur
     */
    public function callInsertFullUserFromJSON(array $params) : array 
    {
        $sql = "CALL InsertFullUserFromJSON(?)";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(1, $params['p_json_data']);
        
        return $stmt->executeQuery()->fetchAllAssociative();
    }
        
    /**
     * Statistique cards claims
     * 
     * @param array $params
     * @return array
     */
    public function callGetUserClaimStats(array $params): array
    {
        $sql = "CALL GetUserClaimStats(?)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_email']);
        
        return $stmt->executeQuery()->fetchAssociative();
    }

    /**
     * Liste des payements d'un utilisateur (dashboard)
     * 
     * @param array $params
     * @return array
     */
    public function callGetPaiementListByUser(array $params): array
    {

       /** @var \PDO $pdo */
        $pdo = $this->connection->getNativeConnection();

        $stmt = $pdo->prepare("CALL GetPaymentListByUser(?, ?, ?, ?, ?, ?, ?, ?, ?)");

        

        $stmt->bindValue(1, $params['p_email']);
        $stmt->bindValue(2, $params['p_status'] ?? null);
        $stmt->bindValue(3, $params['p_invoice_no'] ?? null);
        $stmt->bindValue(4, $params['p_claim_number'] ?? null);
        $stmt->bindValue(5, $params['p_sort_by'] ?? null);
        $stmt->bindValue(6, (int)($params['p_page'] ?? 1));
        $stmt->bindValue(7, (int)($params['p_page_size'] ?? 10));
        $stmt->bindValue(8, $params['p_start_date'] ?? null);
        $stmt->bindValue(9, $params['p_end_date'] ?? null);

        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($stmt->nextRowset()) {
            $meta = $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            $meta = [];
        }

        return array_merge(
            ['data' => $data],
            $meta ?: []
        );

    }

    /**
     * Statistique cards paiements
     * 
     * @param array $params
     * @return array
     */
    public function callGetUserPaiementStats(array $params): array
    {
        $sql = "CALL GetUserPaymentStats(?)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_email']);
        
        return $stmt->executeQuery()->fetchAssociative();
    }

    /**
     * Détail d'un paiement
     * 
     * @param array $params
     * @return array
     */
    public function callGetPaymentDetailsByInvoice(array $params): array
    {
        $sql = "CALL GetPaymentDetailsByInvoice(?, ?)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_invoice_no']);
        $stmt->bindValue(2, $params['p_email']);
        
        return $stmt->executeQuery()->fetchAssociative();
    }

    public function callGetClaimPartial(array $params): array
    {
        /** @var \PDO $pdo */
        $pdo = $this->connection->getNativeConnection();

        $stmt = $pdo->prepare("CALL GetClaimPartialInfo(:claim_number, :email)");
        $stmt->bindValue(':claim_number', $params['p_claim_number']);
        $stmt->bindValue(':email', $params['p_email']);
        $stmt->execute();

        $data = [];
        // 1. Vehicle & Survey Information
        $vehicle_surveis = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
        foreach ($vehicle_surveis as $vehicle_survey) {
            $data['vehicle_information']  = [
                'claim_number'              => $vehicle_survey['claim_number'],
                'name'                      => $vehicle_survey['name'],
                'make'                      => $vehicle_survey['make'],
                'model'                     => $vehicle_survey['model'],
                'cc'                        => $vehicle_survey['cc'],
                'fuel_type'                 => $vehicle_survey['fuel_type'],
                'transmission'              => $vehicle_survey['transmission'],
                'engine_no'                 => $vehicle_survey['engine_no'],
                'chassis_no'                => $vehicle_survey['chasis_no'],
                'vehicle_no'                => $vehicle_survey['vehicle_no'],
                'color'                     => $vehicle_survey['color'],
                'odometer_reading'          => $vehicle_survey['odometer_reading'],
                'is_the_vehicle_total_loss' => $vehicle_survey['is_the_vehicle_total_loss'],
                'condition_of_vehicle'      => $vehicle_survey['condition_of_vehicle'],
                'place_of_survey'           => $vehicle_survey['place_of_survey'],
                'point_of_impact'           => $vehicle_survey['point_of_impact'],
                

            ];
            $data['survey_information'] = [
                'garage'                => $vehicle_survey['garage'],
                'garage_address'        => $vehicle_survey['garage_address'],
                'garage_contact_number' => $vehicle_survey['garage_contact_no'],
                'eor_value'             => $vehicle_survey['eor_value'],
                'invoice_number'        => $vehicle_survey['invoice_number'],
                'survey_type'           => $vehicle_survey['survey_type'],
                'date_of_survey'        => $vehicle_survey['date_of_survey'],
                'time_of_survey'        => $vehicle_survey['time_of_survey'],
                'pre_accident_valeur'   => $vehicle_survey['pre_accident_valeur'],
                'showroom_price'        => $vehicle_survey['showroom_price'],
                'wrech_value'           => $vehicle_survey['wrech_value'],
                'excess_applicable'     => $vehicle_survey['excess_applicable']

            ];
        }
        // 2. Part details
        $partDetails = [];
        if ($stmt->nextRowset()) {
            $partDetails = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // 3. Labour details
        $labourDetails = [];
        if ($stmt->nextRowset()) {
            $labourDetails = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // On regroupe labourDetails par part_detail_id
        $labourGrouped = [];
        foreach ($labourDetails as $labour) {
            $pid = $labour['part_detail_id'];
            $labourGrouped[$pid][] = $labour;
        }

        // Construire la structure imbriquée "data"
        $data['repair_estimate'] = [];
        foreach ($partDetails as $part) {
            $pid = $part['part_detail_id'];

            $data['repair_estimate'][] = [
                'name'          => $part['part_name'],
                'quantity'      => (int) $part['quantity'],
                'part_details'  => [[
                    'part_detail_id' => $part['part_detail_id'],
                    'part_name'      => $part['part_name'],
                    'supplier'       => $part['supplier'],
                    'quality'        => $part['quality'],
                    'cost_part'      => $part['cost_part'],
                    'discount_part'  => $part['discount_part'],
                    'vat_part'       => $part['vat_part'],
                    'part_total'     => $part['part_total'],
                ]],
                'labour_details' => $labourGrouped[$pid] ?? []
            ];
        }


        // 4. Additional labour details
        if ($stmt->nextRowset()) {
            $data['additional_labour_details'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        //Total
        if ($stmt->nextRowset()) {
        $totals = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($totals) {
            $data['grand_total'] = [
                'part' => [
                    'cost_part'     => (float)$totals['cost_part'],
                    'discount_part' => (float)$totals['discount_part'],
                    'vat_part'      => (float)$totals['vat_part'],
                    'part_total'    => (float)$totals['part_total'],
                ],
                'labour' => [
                    'cost_labour' => (float)$totals['cost_labour'],
                    'discount_labour'     => (float)$totals['discount_labour'],
                    'vat_labour'          => (float)$totals['vat_labour'],
                    'labour_total'        => (float)$totals['labour_total'],
                ],
                'overall' => [
                    'cost_total'     => (float)$totals['cost_total'],
                    'discount_total' => (float)$totals['discount_total'],
                    'vat_total'      => (float)$totals['vat_total'],
                    'total'          => (float)$totals['total'],
                ],
            ];
        }
    }
        // Retour final
        return $data;
    }

    /* Statistique cards paiements
     * 
     * @param array $params
     * @return array
     */
    public function callCountNotifications(array $params): array
    {
        $sql = "CALL CountNotifications(?)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_user_id']);
        
        return $stmt->executeQuery()->fetchAssociative();
    }

    /* Statistique cards paiements
     * 
     * @param array $params
     * @return array
     */
    public function callGetListNotificationsById(array $params): array
    {
        $sql = "CALL GetListNotificationsById(?)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $params['p_user_id']);
        
        return $stmt->executeQuery()->fetchAssociative();
    }

}